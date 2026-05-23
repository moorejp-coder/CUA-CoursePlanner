<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;

class UploadController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file      = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'pdf'])) {
            return response()->json(
                ['error' => 'Only .csv and .pdf files are accepted.'],
                422,
            );
        }

        if ($extension === 'pdf') {
            $parser = new PdfParser;
            $pdf    = $parser->parseFile($file->path());
            $text   = $pdf->getText();
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
     * Keeps the token count well under 4,000 by using status symbols and
     * removing whitespace from course codes.
     */
    private function buildApwPrompt(string $rawCsv): string
    {
        // Parse CSV from string via in-memory stream
        $rows   = [];
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $rawCsv);
        rewind($stream);
        // Empty string escape = RFC 4180 compliant; suppresses PHP 8.4 deprecation
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $rows[] = array_map(fn ($c) => trim((string) $c), $row);
        }
        fclose($stream);

        if (empty($rows)) {
            return '';
        }

        // Status → symbol map (case-insensitive)
        $statusMap = [
            'course completed'        => '✓',
            'course in progress'      => '→',
            'course planned'          => 'P',
            'course needs planning 0' => '✗',
        ];

        $sym  = fn (string $v): ?string => $statusMap[strtolower($v)] ?? null;
        $code = fn (string $v): string  => preg_replace('/\s+/', '', $v);

        // ── Student info: column A = label, column B = value ─────────
        $fieldMap = [
            'name'     => ['last name, first name', 'last name/first name', 'student name', 'last name'],
            'degree'   => ['degree'],
            'admit'    => ['admit term'],
            'grad'     => ['expected graduation term', 'expected graduation'],
            'cr_done'  => ['credits completed'],
            'cr_ip'    => ['credits in progress'],
            'cr_plan'  => ['credits planned'],
            'standing' => ['projected standing'],
            'gpa'      => ['cum gpa'],
            'math'     => ['math placement'],
            'lang'     => ['foreign language placement', 'language placement'],
        ];

        $info = array_fill_keys(array_keys($fieldMap), '?');

        foreach ($rows as $row) {
            $a = strtolower($row[0] ?? '');
            $b = $row[1] ?? '';
            if ($a === '') {
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

        // ── Locate section headers (row + col) ───────────────────────
        $kwMap = [
            'degree' => 'degree requirements',
            'spec'   => 'specialization requirements',
            'la'     => 'liberal arts requirements',
            'elec'   => 'free electives',
        ];

        $hdrPos = [];

        for ($r = 0, $total = count($rows); $r < $total; $r++) {
            foreach ($rows[$r] as $c => $cell) {
                $lower = strtolower($cell);
                foreach ($kwMap as $key => $kw) {
                    if (! isset($hdrPos[$key]) && str_contains($lower, $kw)) {
                        $hdrPos[$key] = ['row' => $r, 'col' => $c];
                    }
                }
            }
            if (count($hdrPos) === count($kwMap)) {
                break;
            }
        }

        // ── Derive per-section column ranges from header columns ──────
        // Each section owns columns from its header column up to (but not
        // including) the next section's header column, sorted left→right.
        $byCol = array_map(fn ($h) => $h['col'], $hdrPos);
        asort($byCol);
        $sortedKeys = array_keys($byCol);
        $colRanges  = [];

        for ($i = 0; $i < count($sortedKeys); $i++) {
            $k   = $sortedKeys[$i];
            $min = $byCol[$k];
            $max = isset($sortedKeys[$i + 1]) ? $byCol[$sortedKeys[$i + 1]] - 1 : PHP_INT_MAX;
            $colRanges[$k] = [$min, $max];
        }

        // ── Find spec section's status + course columns ───────────────
        $specStatusCol = null;
        $specCourseCol = null;

        if (isset($colRanges['spec'])) {
            [$specMin, $specMax] = $colRanges['spec'];
            foreach ($rows as $row) {
                for ($c = $specMin; $c <= min($specMax, count($row) - 1); $c++) {
                    if ($sym($row[$c]) !== null) {
                        $specStatusCol = $c;
                        $specCourseCol = $c > 0 ? $c - 1 : $c + 1;
                        break 2;
                    }
                }
            }
        }

        // ── Collect courses + detect specialization name labels ───────
        $coreCourses = [];
        $laCourses   = [];
        $elecCourses = [];
        $specBlocks  = []; // [['name' => string, 'courses' => string[]]]
        $curSpec     = -1;

        foreach ($rows as $row) {
            // Course/status pair scan — every column
            foreach ($row as $c => $cell) {
                $s = $sym($cell);
                if ($s === null) {
                    continue;
                }

                $left  = $c > 0 ? ($row[$c - 1] ?? '') : '';
                $right = $row[$c + 1] ?? '';
                $name  = $left !== '' ? $left : $right;
                if ($name === '') {
                    continue;
                }

                $entry = $code($name).$s;

                // Attribute to section by column
                foreach ($colRanges as $key => [$min, $max]) {
                    if ($c < $min || $c > $max) {
                        continue;
                    }
                    match ($key) {
                        'degree' => ($coreCourses[] = $entry),
                        'la'     => ($laCourses[]   = $entry),
                        'elec'   => ($elecCourses[]  = $entry),
                        'spec'   => ($curSpec >= 0 ? ($specBlocks[$curSpec]['courses'][] = $entry) : null),
                        default  => null,
                    };
                    break;
                }
            }

            // Specialization name label: non-empty course col, empty/non-status status col
            if ($specCourseCol !== null && $specStatusCol !== null) {
                $nameCell   = $row[$specCourseCol] ?? '';
                $statusCell = $row[$specStatusCol] ?? '';

                if (
                    $nameCell !== ''
                    && $sym($statusCell) === null
                    && ! preg_match('/^[A-Z]{2,6}\s*\d{3}/i', $nameCell)
                    && ! str_contains(strtolower($nameCell), 'specialization')
                    && ! str_contains(strtolower($nameCell), 'requirements')
                ) {
                    $specBlocks[] = ['name' => $nameCell, 'courses' => []];
                    $curSpec      = count($specBlocks) - 1;
                }
            }
        }

        // ── Build compact output ──────────────────────────────────────
        $n   = $info;
        $out = sprintf(
            'APW: %s | %s | Admit %s | GPA %s | %s | Cr: %s/%s/%s | Grad: %s | Math: %s | Lang: %s',
            $n['name'], $n['degree'], $n['admit'], $n['gpa'], $n['standing'],
            $n['cr_done'], $n['cr_ip'], $n['cr_plan'], $n['grad'], $n['math'], $n['lang']
        );

        if (! empty($coreCourses)) {
            $out .= "\n\nCORE: ".implode(' ', $coreCourses);
        }

        $ordinals = ['SPEC1', 'SPEC2', 'SPEC3'];
        foreach (array_slice($specBlocks, 0, 3) as $i => $spec) {
            if (empty($spec['courses'])) {
                continue;
            }
            $out .= "\n".($ordinals[$i] ?? 'SPEC'.($i + 1)).' '.$spec['name'].': '.implode(' ', $spec['courses']);
        }

        if (! empty($laCourses)) {
            $out .= "\n\nLIBARTS: ".implode(' ', $laCourses);
        }

        if (! empty($elecCourses)) {
            $out .= "\n\nELECTIVES: ".implode(' ', $elecCourses);
        }

        // Fallback: if section detection failed entirely, dump all pairs flat
        if (empty($coreCourses) && empty($laCourses) && empty($elecCourses) && empty($specBlocks)) {
            $all = [];
            foreach ($rows as $row) {
                foreach ($row as $c => $cell) {
                    $s = $sym($cell);
                    if ($s === null) {
                        continue;
                    }
                    $left  = $c > 0 ? ($row[$c - 1] ?? '') : '';
                    $right = $row[$c + 1] ?? '';
                    $name  = $left !== '' ? $left : $right;
                    if ($name !== '') {
                        $all[] = $code($name).$s;
                    }
                }
            }
            if (! empty($all)) {
                $out .= "\n\nCOURSES: ".implode(' ', $all);
            }
        }

        $out .= "\n\nAnalyze this student's BSBA/BSAccounting progress. List what still needs completing, flag any issues, and give specific actionable advice.";

        return $out;
    }
}
