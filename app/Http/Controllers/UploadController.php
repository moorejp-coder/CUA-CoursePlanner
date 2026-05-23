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
            $pdf    = $parser->parseFile($file->path());
            $text   = $pdf->getText();
        } else {
            $rawCsv = file_get_contents($file->path());
            $text   = $this->buildApwPrompt((string) $rawCsv);
        }

        if (empty(trim((string) $text))) {
            return response()->json(
                ['error' => 'No text could be extracted from this file. Make sure it is not a scanned image.'],
                422,
            );
        }

        return response()->json(['text' => $text]);
    }

    private function buildApwPrompt(string $rawCsv): string
    {
        $instructions = <<<'INSTRUCTIONS'
You are reading a Busch School of Business Academic Planning Worksheet (APW) exported as CSV. Here is how to read it:

The CSV has multiple column groups side by side:
- Columns A-D: Student info (left side) and Degree Requirements with grades and statuses
- Columns E-H: Junior/Senior degree requirements
- Columns I-K: Specialization requirements (up to 3 specializations stacked)
- Columns L-M: Liberal Arts requirements
- Columns N-O: Free Electives

Course status values mean:
- 'Course Completed' = student has finished this course (look for a grade like A, B+, etc. in the adjacent column)
- 'Course in Progress' = student is currently enrolled
- 'Course Planned' = student has scheduled this for a future semester
- 'Course Needs Planning 0' = not yet planned, still required

Student info is in the first ~17 rows on the left: name, student ID, admit term, degree, expected graduation, credits completed, credits in progress, credits planned, projected standing, CUM GPA, math placement, language placement.

Read every single row carefully. Do NOT assume a course is incomplete just because you cannot find it — look at the status column next to each course name.

Here is the raw APW CSV data:
INSTRUCTIONS;

        $footer = <<<'FOOTER'

Based on this data, provide:
1. Student name, degree program, admit term, catalog year (pre or post Spring 2024)
2. Credits completed, in progress, and remaining to graduation
3. CUM GPA and projected standing
4. Every course marked Course Completed — list them all
5. Every course marked Course in Progress
6. Every course still needing planning
7. All specializations detected and their completion status
8. What the student still needs to graduate
9. Any warnings or concerns
FOOTER;

        return $instructions."\n".$rawCsv.$footer;
    }
}
