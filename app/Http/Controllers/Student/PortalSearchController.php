<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\PortalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API recherche instantanée du portail fidèle.
 */
class PortalSearchController extends Controller
{
  /**
   * @param  PortalSearchService  $searchService  Service de recherche
   */
  public function __construct(
    private readonly PortalSearchService $searchService,
  ) {}

  /**
   * Retourne les suggestions JSON pour la barre de recherche.
   */
  public function index(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'q' => ['required', 'string', 'min:2', 'max:120'],
      'context' => ['nullable', 'string', 'in:global,ecap_questions,ecap_staff'],
    ]);

    return response()->json(
      $this->searchService->search(
        $request->user('member'),
        $validated['q'],
        $validated['context'] ?? 'global',
      ),
    );
  }
}
