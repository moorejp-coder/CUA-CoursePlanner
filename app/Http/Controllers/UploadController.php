<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class UploadController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['csv', 'pdf'];

    private const ALLOWED_MIMES = [
        'csv' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'],
        'pdf' => ['application/pdf'],
    ];

    public function handle(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Reject filenames with special characters or double extensions
        if (! preg_match('/^[\w\-. ]+$/', $originalName)) {
            return response()->json(
                ['error' => 'Invalid filename. Use only letters, numbers, hyphens, and underscores.'],
                422,
            );
        }

        // Reject double extensions (e.g. file.php.csv)
        $parts = explode('.', $originalName);
        if (count($parts) > 2) {
            return response()->json(
                ['error' => 'Invalid filename. Only one file extension is allowed.'],
                422,
            );
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return response()->json(
                ['error' => 'Only .csv and .pdf files are accepted.'],
                422,
            );
        }

        // Server-side MIME type validation using finfo (not just the extension)
        $detectedMime = $file->getMimeType();
        if (! in_array($detectedMime, self::ALLOWED_MIMES[$extension])) {
            return response()->json(
                ['error' => 'File content does not match the expected type for .'.$extension.'.'],
                422,
            );
        }

        if ($extension === 'pdf') {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($file->path());
            $text = $pdf->getText();
        } else {
            $text = $this->buildApwPrompt((string) file_get_contents($file->path()));
        }

        if (empty(trim((string) $text))) {
            return response()->json(
                ['error' => 'No text could be extracted from this file. Make sure it is not a scanned image.'],
                422,
            );
        }

        return response()->json(['text' => $text]);
    }

    /**
     * Parse the raw APW CSV and produce an ultra-compact prompt for Groq.
     *
     * Handles all 4 APW template versions by detecting column layout from
     * the column count (25 = BSBA, 22 = BS Accounting) and using fixed column
     * positions derived from template analysis rather than keyword scanning.
     *
     * Column layout (confirmed against all 4 template versions + student APW):
     *   All versions — Core: (col2,col4) First-Year, (col5,col7) Junior/Senior
     *   BSBA (25 cols) — Spec: col8=label/course, col10=status; LA: (col11,col13); Elec: (col14,col16)
     *   BSA  (22 cols) — LA: (col8,col10); Elec: (col11,col13)
     */
    private function buildApwPrompt(string $rawCsv): string
    {
        $rows = [];
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $rawCsv);
        rewind($stream);
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $rows[] = array_map(fn ($c) => trim((string) $c), $row);
        }
        fclose($stream);

        if (empty($rows)) {
            return '';
        }

        $statusMap = [
            'course completed' => '✓',
            'course in progress' => '→',
            'course planned' => 'P',
            'course needs planning 0' => '✗',
            'course needs to be planned' => '✗',
        ];

        $sym = fn (string $v): ?string => $statusMap[strtolower(trim($v))] ?? null;
        $compress = fn (string $v): string => preg_replace('/\s+/', '', $v);

        // 25 cols = BSBA (with specialization section), 22 cols = BS Accounting
        $maxCols = max(array_map('count', $rows));
        $isBSBA = $maxCols >= 25;

        // ── Student info (col0 = label, col1 = value) ────────────────
        $fieldMap = [
            'name' => ['last name, first name', 'last name/first name', 'last name, first', 'last name'],
            'degree' => ['degree'],
            'admit' => ['admit term'],
            'grad' => ['expected graduation term', 'expected graduation'],
            'cr_done' => ['credits completed'],
            'cr_ip' => ['credit in progress', 'credits in progress'],
            'cr_plan' => ['credits planned', 'planned credits'],
            'standing' => ['standing'],
            'gpa' => ['cum gpa'],
            'math' => ['math placement'],
            'lang' => ['foreign language placement', 'language placement'],
        ];

        $info = array_fill_keys(array_keys($fieldMap), '?');

        foreach ($rows as $row) {
            $a = strtolower($row[0] ?? '');
            $b = $row[1] ?? '';
            // Skip empty labels and long compound rows (e.g. "Credits Completed + … + Credits Planned")
            if ($a === '' || strlen($a) > 50) {
                continue;
            }
            foreach ($fieldMap as $key => $variants) {
                if ($info[$key] !== '?') {
                    continue;
                }
                foreach ($variants as $v) {
                    if (str_contains($a, $v)) {
                        $info[$key] = $b !== '' ? $b : '?';
                        break;
                    }
                }
            }
        }

        // Students replace the "Last Name, First Name" label in row0/col0 with their actual name
        if ($info['name'] === '?' && isset($rows[0][0])) {
            $cell0 = trim($rows[0][0]);
            $lower0 = strtolower($cell0);
            if ($cell0 !== '' && ! str_contains($lower0, 'last name') && ! str_contains($lower0, 'student')) {
                $info['name'] = $cell0;
            }
        }

        // ── Data range: stop at the LEGEND row (col2 = 'LEGEND') ─────
        $dataEnd = count($rows);
        foreach ($rows as $ri => $row) {
            if (($row[2] ?? '') === 'LEGEND' || str_starts_with($row[0] ?? '', 'Frequently Asked')) {
                $dataEnd = $ri;
                break;
            }
        }

        // ── Fixed column pairs (course_col, status_col) ───────────────
        $corePairs = [[2, 4], [5, 7]]; // First-Year(/Sophomore) + Junior/Senior

        if ($isBSBA) {
            $laPairs = [[11, 13]];
            $elecPairs = [[14, 16]];
            // Specialization: col8 = course name (or spec-block label), col10 = status
        } else {
            $laPairs = [[8, 10]];
            $elecPairs = [[11, 13]];
        }

        // Cell values that are headers/annotations, not course codes
        $headerTokens = [
            'degree requirements', 'specialization requirements', 'liberal arts requirements',
            'free electives', 'first-year', 'junior/senior', 'first-year/sophomore',
            'business minors', 'four year plan', 'transfer credits', 'did not satisfy',
            'legend', 'math fulfilled', 'social science fulfilled', 'business elective',
            'sophomore', 'course completed', 'course in progress', 'course planned', 'course needs',
        ];

        $isHeader = static function (string $cell) use ($headerTokens): bool {
            if ($cell === '' || is_numeric($cell)) {
                return true;
            }
            $lower = strtolower($cell);
            foreach ($headerTokens as $t) {
                if (str_contains($lower, $t)) {
                    return true;
                }
            }

            return false;
        };

        // ── Collect courses ───────────────────────────────────────────
        $coreCourses = [];
        $laCourses = [];
        $elecCourses = [];
        $specBlocks = []; // [['name' => string, 'courses' => string[]]]
        $curSpec = -1;

        // Start at row 1 so specialization block labels in row1 are detected;
        // course collection is guarded to start at row 2.
        for ($ri = 1; $ri < $dataEnd; $ri++) {
            $row = $rows[$ri] ?? [];

            // Specialization block label detection (BSBA only).
            // col8 is the spec-name label when col10 is not a status value
            // (credit count, empty) and col8 is not a course-code pattern.
            if ($isBSBA) {
                $col8 = $row[8] ?? '';
                $col10 = $row[10] ?? '';
                $looksLikeCourse = preg_match('/^[A-Z]{2,6}\s*\d/i', $col8);
                if ($col8 !== '' && $sym($col10) === null && ! $looksLikeCourse) {
                    $specBlocks[] = ['name' => $col8, 'courses' => []];
                    $curSpec = count($specBlocks) - 1;
                }
            }

            if ($ri < 2) {
                continue; // Row 1 sub-headers are labels only, not course data
            }

            // Core degree courses
            foreach ($corePairs as [$cc, $sc]) {
                $course = $row[$cc] ?? '';
                $status = $row[$sc] ?? '';
                $s = $sym($status);
                if ($s !== null && ! $isHeader($course)) {
                    $coreCourses[] = $compress($course).$s;
                }
            }

            // Specialization courses (BSBA only): col8 = course, col10 = status
            if ($isBSBA) {
                $course = $row[8] ?? '';
                $status = $row[10] ?? '';
                $s = $sym($status);
                if ($s !== null && ! $isHeader($course)) {
                    if ($curSpec >= 0) {
                        $specBlocks[$curSpec]['courses'][] = $compress($course).$s;
                    } else {
                        $specBlocks[] = ['name' => 'Specialization', 'courses' => [$compress($course).$s]];
                        $curSpec = 0;
                    }
                }
            }

            // Liberal Arts
            foreach ($laPairs as [$cc, $sc]) {
                $course = $row[$cc] ?? '';
                $status = $row[$sc] ?? '';
                $s = $sym($status);
                if ($s !== null && ! $isHeader($course)) {
                    $laCourses[] = $compress($course).$s;
                }
            }

            // Free electives
            foreach ($elecPairs as [$cc, $sc]) {
                $course = $row[$cc] ?? '';
                $status = $row[$sc] ?? '';
                $s = $sym($status);
                if ($s !== null && ! $isHeader($course)) {
                    $elecCourses[] = $compress($course).$s;
                }
            }
        }

        // ── Build compact output ──────────────────────────────────────
        $n = $info;
        $out = "KEY: ✓=completed →=in-progress P=planned ✗=not-started\n\n";
        $out .= 'TEMPLATE: '.($isBSBA ? 'BSBA' : 'BS Accounting')."\n";
        $out .= sprintf(
            'APW: %s | %s | Admit %s | GPA %s | %s | Cr: %s done/%s in-progress/%s planned | Grad: %s | Math: %s | Lang: %s',
            $n['name'], $n['degree'], $n['admit'], $n['gpa'], $n['standing'],
            $n['cr_done'], $n['cr_ip'], $n['cr_plan'], $n['grad'], $n['math'], $n['lang']
        );

        if (! empty($coreCourses)) {
            $out .= "\n\nCORE: ".implode(' ', $coreCourses);
        }

        if ($isBSBA) {
            $ordinals = ['SPEC1', 'SPEC2', 'SPEC3'];
            foreach (array_slice($specBlocks, 0, 3) as $i => $spec) {
                if (empty($spec['courses'])) {
                    continue;
                }
                $out .= "\n".($ordinals[$i] ?? 'SPEC'.($i + 1)).' '.$spec['name'].': '.implode(' ', $spec['courses']);
            }
        }

        if (! empty($laCourses)) {
            $out .= "\n\nLIBARTS: ".implode(' ', $laCourses);
        }

        if (! empty($elecCourses)) {
            $out .= "\n\nELECTIVES: ".implode(' ', $elecCourses);
        }

        $out .= "\n\nSECTION KEY:\n"
            ."- CORE = required business core courses for the degree\n"
            ."- SPEC1/SPEC2/SPEC3 = specialization requirement courses (each specialization listed separately)\n"
            ."- LIBARTS = liberal arts and general education requirements\n"
            ."- ELECTIVES = free elective credits\n"
            ."\nUsing the above Academic Planning Worksheet data, provide a thorough academic advising response. Address:\n"
            ."1. Which required courses (CORE, SPEC, LIBARTS) still have ✗ status and need to be planned\n"
            ."2. Whether any in-progress (→) or planned (P) courses have prerequisites not yet completed (✓)\n"
            ."3. Whether the student is on track to graduate by their expected graduation term\n"
            ."4. Any critical rules that apply (e.g. MGT 475 + BUS 498 same semester, credit gate requirements, SRES 290 if post-Spring 2024)\n"
            .'5. Recommended next steps and any concerns to raise with their academic advisor';

        Log::info('APW parsed', [
            'degree' => $n['degree'],
            'standing' => $n['standing'],
            'grad' => $n['grad'],
            'template' => $isBSBA ? 'BSBA' : 'BSA',
        ]);

        return $out;
    }
}
