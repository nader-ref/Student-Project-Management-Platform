<?php

namespace App\Http\Controllers;

use App\Services\ProjectProposalAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiProposalController extends Controller
{
    public function suggest(Request $request, ProjectProposalAssistantService $assistant): JsonResponse
    {
        // Herd/web PHP often uses max_execution_time=30, while CLI is commonly 0 (unlimited).
        // Ollama may need 25–50s; raise the limit for this endpoint only.
        set_time_limit(120);

        $maxChars = max(20, (int) config('ai.max_input_chars', 2000));

        $validated = $request->validate([
            'raw_idea' => ['required', 'string', 'min:20', 'max:'.$maxChars],
        ]);

        $result = $assistant->suggest($validated['raw_idea']);

        return response()->json($result);
    }
}
