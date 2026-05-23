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
            $text = file_get_contents($file->path());
        }

        if (empty(trim((string) $text))) {
            return response()->json(
                ['error' => 'No text could be extracted from this file. Make sure it is not a scanned image.'],
                422,
            );
        }

        return response()->json(['text' => $text]);
    }
}
