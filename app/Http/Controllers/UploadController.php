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

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'pdf'])) {
            return response()->json(
                ['error' => 'Only .csv and .pdf files are accepted.'],
                422,
            );
        }

        if ($extension === 'pdf') {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($file->path());
            $text = $pdf->getText();
        } else {
            $text = $this->parseApwCsv($file->path());
        }

        if (empty(trim((string) $text))) {
            return response()->json(
                ['error' => 'No text could be extracted from this file. Make sure it is not a scanned image.'],
                422,
            );
        }

        return response()->json(['text' => $text]);
    }

    private function parseApwCsv(string $path): string
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return '';
        }

        $statuses = [
            'Course Completed',
            'Course In Progress',
            'Course Planned',
            'Course Needs Planning 0',
        ];

        // Student info: scan column A for known labels, value is in column B
        $infoFields = [
            'name'         => ['last name, first name', 'last name/first name', 'student name'],
            'id'           => ['student id'],
            'admit_term'   => ['admit term'],
            'degree'       => ['degree'],
            'grad_term'    => ['expected graduation term', 'expected graduation'],
            'credits_comp' => ['credits completed'],
            'credits_ip'   => ['credits in progress'],
            'credits_plan' => ['credits planned'],
            'standing'     => ['projected standing'],
            'gpa'          => ['cum gpa'],
            'math'         => ['math placement'],
            'language'     => ['foreign language placement', 'language placement'],
            'advisor'      => ['faculty advisor'],
        ];

        $info = array_fill_keys(array_keys($infoFields), '');

        foreach ($rows as $row) {
            if (empty($row[0])) {
                continue;
            }
            $cellA = strtolower(trim((string) $row[0]));
            $cellB = isset($row[1]) ? trim((string) $row[1]) : '';

            foreach ($infoFields as $key => $variants) {
                if (! empty($info[$key])) {
                    continue;
                }
                foreach ($variants as $variant) {
                    if (str_contains($cellA, $variant)) {
                        $info[$key] = $cellB;
                        break;
                    }
                }
            }
        }

        // Locate section header rows by scanning every cell
        $sectionKeywords = [
            'degree requirements',
            'specialization requirements',
            'liberal arts requirements',
            'free electives',
        ];

        $sectionStarts = []; // row_index => header label
        for ($i = 0; $i < count($rows); $i++) {
            foreach ($rows[$i] as $cell) {
                $lower = strtolower(trim((string) $cell));
                foreach ($sectionKeywords as $kw) {
                    if (str_contains($lower, $kw)) {
                        $sectionStarts[$i] = trim((string) $cell);
                        break 2;
                    }
                }
            }
        }

        // Process each section
        $sectionIndices  = array_keys($sectionStarts);
        $sectionCourses  = []; // degree requirements, liberal arts, free electives
        $specializations = []; // parsed specialization sub-blocks

        for ($s = 0; $s < count($sectionIndices); $s++) {
            $start  = $sectionIndices[$s] + 1;
            $end    = isset($sectionIndices[$s + 1]) ? $sectionIndices[$s + 1] - 1 : count($rows) - 1;
            $header = $sectionStarts[$sectionIndices[$s]];

            if (stripos($header, 'specialization') !== false) {
                $specializations = $this->extractSpecializations($rows, $start, $end, $statuses);
            } else {
                $courses = array_fill_keys($statuses, []);

                for ($i = $start; $i <= $end; $i++) {
                    if (! isset($rows[$i])) {
                        continue;
                    }
                    $row = $rows[$i];

                    foreach ($row as $colIdx => $cell) {
                        $cellTrimmed = trim((string) $cell);
                        if (! in_array($cellTrimmed, $statuses)) {
                            continue;
                        }

                        $left  = trim((string) ($row[$colIdx - 1] ?? ''));
                        $right = trim((string) ($row[$colIdx + 1] ?? ''));

                        $courseName = ($colIdx > 0 && $left !== '') ? $left : $right;

                        if ($courseName !== '') {
                            $courses[$cellTrimmed][] = $courseName;
                        }
                        break;
                    }
                }

                $sectionCourses[$header] = $courses;
            }
        }

        $fmt = fn (array $arr): string => empty($arr) ? 'None' : implode(', ', $arr);

        // Build structured summary
        $s  = "STUDENT ACADEMIC PLANNING WORKSHEET SUMMARY:\n";
        $s .= '- Student: '.($info['name'] ?: 'Unknown')."\n";
        $s .= '- Degree: '.($info['degree'] ?: 'Unknown')."\n";
        $s .= '- Admit Term: '.($info['admit_term'] ?: 'Unknown')."\n";
        $s .= '- Expected Graduation: '.($info['grad_term'] ?: 'Unknown')."\n";
        $s .= '- Credits Completed: '.($info['credits_comp'] ?: 'Unknown')."\n";
        $s .= '- Credits In Progress: '.($info['credits_ip'] ?: 'Unknown')."\n";
        $s .= '- Credits Planned: '.($info['credits_plan'] ?: 'Unknown')."\n";
        $s .= '- Projected Standing: '.($info['standing'] ?: 'Unknown')."\n";
        $s .= '- CUM GPA: '.($info['gpa'] ?: 'Unknown')."\n";
        $s .= '- Math Placement: '.($info['math'] ?: 'Unknown')."\n";
        $s .= '- Language Placement: '.($info['language'] ?: 'Unknown')."\n\n";

        // Degree Requirements
        foreach (array_keys($sectionCourses) as $k) {
            if (stripos($k, 'degree requirements') !== false) {
                $dr = $sectionCourses[$k];
                $s .= "DEGREE REQUIREMENTS STATUS:\n";
                $s .= 'Completed: '.$fmt($dr['Course Completed'])."\n";
                $s .= 'In Progress: '.$fmt($dr['Course In Progress'])."\n";
                $s .= 'Planned: '.$fmt($dr['Course Planned'])."\n";
                $s .= 'Needs Planning: '.$fmt($dr['Course Needs Planning 0'])."\n\n";
                break;
            }
        }

        // Specialization Requirements — per named specialization
        if (! empty($specializations)) {
            $s .= "SPECIALIZATION REQUIREMENTS:\n";
            $ordinals = ['1st', '2nd', '3rd', '4th'];
            foreach ($specializations as $idx => $spec) {
                $ord = $ordinals[$idx] ?? ($idx + 1).'th';
                $s .= "{$ord} Specialization - {$spec['name']}:\n";
                $s .= '  Completed: '.$fmt($spec['courses']['Course Completed'])."\n";
                $s .= '  In Progress: '.$fmt($spec['courses']['Course In Progress'])."\n";
                $s .= '  Planned: '.$fmt($spec['courses']['Course Planned'])."\n";
                $s .= '  Needs Planning: '.$fmt($spec['courses']['Course Needs Planning 0'])."\n";
            }
            $s .= "\n";
        }

        // Liberal Arts Requirements
        foreach (array_keys($sectionCourses) as $k) {
            if (stripos($k, 'liberal arts') !== false) {
                $la = $sectionCourses[$k];
                $s .= "LIBERAL ARTS STATUS:\n";
                $s .= 'Completed: '.$fmt($la['Course Completed'])."\n";
                $s .= 'In Progress: '.$fmt($la['Course In Progress'])."\n";
                $s .= 'Planned: '.$fmt($la['Course Planned'])."\n";
                $s .= 'Needs Planning: '.$fmt($la['Course Needs Planning 0'])."\n\n";
                break;
            }
        }

        // Free Electives
        foreach (array_keys($sectionCourses) as $k) {
            if (stripos($k, 'free elective') !== false) {
                $fe = $sectionCourses[$k];
                $s .= "FREE ELECTIVES STATUS:\n";
                $s .= 'Completed: '.$fmt($fe['Course Completed'])."\n";
                $s .= 'In Progress: '.$fmt($fe['Course In Progress'])."\n";
                $s .= 'Planned: '.$fmt($fe['Course Planned'])."\n";
                $s .= 'Needs Planning: '.$fmt($fe['Course Needs Planning 0'])."\n\n";
                break;
            }
        }

        $s .= "Please analyze this student's APW and:\n";
        $s .= "1. Confirm what degree program and catalog year they are on\n";
        $s .= "2. List exactly what required courses they still need to complete\n";
        $s .= "3. Flag any prerequisite issues based on what is completed vs planned\n";
        $s .= "4. Identify how many credits they still need\n";
        $s .= "5. Note any concerns or warnings";

        return $s;
    }

    /**
     * Within a Specialization Requirements section, find each named specialization
     * (e.g. "Marketing", "Sales") and group its courses by status.
     *
     * Specialization label rows have a name in the course column but nothing in the
     * status column. Course rows have both a name and a status value.
     *
     * @return array<int, array{name: string, courses: array<string, list<string>>}>
     */
    private function extractSpecializations(array $rows, int $start, int $end, array $statuses): array
    {
        // Find which column contains status values for this section
        $statusCol = -1;
        for ($i = $start; $i <= $end; $i++) {
            if (! isset($rows[$i])) {
                continue;
            }
            foreach ($rows[$i] as $colIdx => $cell) {
                if (in_array(trim((string) $cell), $statuses)) {
                    $statusCol = $colIdx;
                    break 2;
                }
            }
        }

        if ($statusCol < 0) {
            return [];
        }

        // Course names sit immediately to the left of the status column (or right if at col 0)
        $courseCol = $statusCol > 0 ? $statusCol - 1 : $statusCol + 1;

        $specs           = [];
        $currentSpecIdx  = -1;

        for ($i = $start; $i <= $end; $i++) {
            if (! isset($rows[$i])) {
                continue;
            }
            $row = $rows[$i];

            $courseCell = trim((string) ($row[$courseCol] ?? ''));
            $statusCell = trim((string) ($row[$statusCol] ?? ''));

            if ($courseCell === '') {
                continue;
            }

            if (in_array($statusCell, $statuses)) {
                // Course row — attribute to the current specialization
                if ($currentSpecIdx >= 0) {
                    $specs[$currentSpecIdx]['courses'][$statusCell][] = $courseCell;
                }
            } else {
                // No status value — candidate for a specialization name label.
                // Skip if it looks like a course code (e.g. "MGT 311") or a section header.
                if (preg_match('/^[A-Z]{2,6}\s+\d{3}/i', $courseCell)) {
                    continue;
                }
                if (str_contains(strtolower($courseCell), 'specialization requirements')) {
                    continue;
                }

                $specs[] = [
                    'name'    => $courseCell,
                    'courses' => array_fill_keys($statuses, []),
                ];
                $currentSpecIdx = count($specs) - 1;
            }
        }

        return $specs;
    }
}
