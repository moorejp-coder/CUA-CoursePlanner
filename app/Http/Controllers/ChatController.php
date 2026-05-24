<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        return view('chat');
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'array|max:50',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:4000',
        ]);

        $cleanMessage = strip_tags($validated['message']);

        $systemPrompt = file_get_contents(storage_path('app/system_prompt.txt'));

        $formattingRule = "\n\nFORMATTING RULE: Never use markdown bold formatting (** **) in your responses. Use plain text, dashes, or numbered lists only.";

        $messages = [['role' => 'system', 'content' => $systemPrompt.$formattingRule]];

        foreach ($validated['history'] ?? [] as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => strip_tags($turn['content'])];
        }

        $messages[] = ['role' => 'user', 'content' => $cleanMessage];

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => $messages,
                    'max_tokens' => 4096,
                ]);
        } catch (\Throwable $e) {
            Log::error('Groq API connection error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                ['error' => 'The AI service is temporarily unavailable. Please try again.'],
                502,
            );
        }

        if (! $response->successful()) {
            Log::error('Groq API error response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json(
                ['error' => 'The AI service is temporarily unavailable. Please try again.'],
                502,
            );
        }

        Log::info('Groq API success', [
            'status' => $response->status(),
            'model' => $response->json('model'),
            'usage' => $response->json('usage'),
            'finish_reason' => $response->json('choices.0.finish_reason'),
        ]);

        return response()->json([
            'message' => $response->json('choices.0.message.content'),
        ]);
    }
}
