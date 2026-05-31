<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Legal\LegalDocumentService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consultation publique des documents légaux (PDF).
 */
class LegalDocumentController extends Controller
{
  /**
   * @param  LegalDocumentService  $legalDocumentService  Résolution des documents
   */
  public function __construct(
    private readonly LegalDocumentService $legalDocumentService,
  ) {}

  /**
   * Affiche le PDF dans le navigateur.
   */
  public function show(string $slug): StreamedResponse|Response
  {
    $document = $this->legalDocumentService->activeBySlug($slug);

    if ($document === null || ! $document->fileExists()) {
      abort(404, 'Document introuvable.');
    }

    return Storage::disk('public')->response(
      $document->file_path,
      $document->title.'.pdf',
      ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline'],
    );
  }

  /**
   * Télécharge le PDF.
   */
  public function download(string $slug): StreamedResponse|Response
  {
    $document = $this->legalDocumentService->activeBySlug($slug);

    if ($document === null || ! $document->fileExists()) {
      abort(404, 'Document introuvable.');
    }

    return Storage::disk('public')->download(
      $document->file_path,
      $document->slug.'-'.$document->version.'.pdf',
    );
  }
}
