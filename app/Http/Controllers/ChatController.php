<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        return view('chat');
    }

    public function message(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:100000',
            'history' => 'array|max:50',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:4000',
        ]);

        $systemPrompt = file_get_contents(storage_path('app/system_prompt.txt'));

        $formattingRule = "\n\nFORMATTING RULE: Never use markdown bold formatting (** **) in your responses. Use plain text, dashes, or numbered lists only.";

        $messages = [['role' => 'system', 'content' => $systemPrompt.$formattingRule]];

        foreach ($request->input('history', []) as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $request->message];

        $response = Http::withToken(config('services.groq.key'))
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant',
                'messages' => $messages,
                'max_tokens' => 4096,
            ]);

        if (! $response->successful()) {
            return response()->json(
                ['error' => 'The AI service is temporarily unavailable. Please try again.'],
                502,
            );
        }

        return response()->json([
            'message' => $response->json('choices.0.message.content'),
        ]);
    }
}
