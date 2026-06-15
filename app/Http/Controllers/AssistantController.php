<?php

namespace App\Http\Controllers;

use App\Services\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function __construct(private AssistantService $assistant)
    {
    }

    public function index(): View
    {
        return view('assistant.index');
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'historique' => ['nullable', 'array'],
            'historique.*.role' => ['required_with:historique', 'string', 'in:user,assistant'],
            'historique.*.content' => ['required_with:historique', 'string'],
        ]);

        $historique = array_map(
            fn (array $m) => ['role' => $m['role'] === 'assistant' ? 'assistant' : 'user', 'content' => $m['content']],
            $validated['historique'] ?? []
        );

        $reponse = $this->assistant->repondre(auth()->user(), $validated['message'], $historique);

        return response()->json(['reponse' => $reponse]);
    }
}
