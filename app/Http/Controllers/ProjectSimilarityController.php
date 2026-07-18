<?php

namespace App\Http\Controllers;

use App\Services\Ai\ProjectSimilarityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectSimilarityController extends Controller
{
    public function check(Request $request, ProjectSimilarityService $similarity): JsonResponse
    {
        // Embedding batches may take longer than default web max_execution_time.
        set_time_limit(120);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'proposal_description' => ['nullable', 'string', 'max:5000'],
        ]);

        $result = $similarity->compare(
            $validated['title'],
            $validated['proposal_description'] ?? null,
        );

        return response()->json($result);
    }
}
