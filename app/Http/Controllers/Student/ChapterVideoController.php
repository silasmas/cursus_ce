<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Services\Student\ChapterProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Diffusion sécurisée des vidéos hébergées sur la plateforme.
 */
class ChapterVideoController extends Controller
{
  /**
   * @param  ChapterProgressService  $progressService  Contrôle d'accès aux chapitres
   */
  public function __construct(
    private readonly ChapterProgressService $progressService,
  ) {}

  /**
   * Stream une vidéo de chapitre pour le fidèle authentifié (lecteur HTML5 natif).
   *
   * @param  Request  $request  Requête HTTP (Range supporté)
   * @param  ContentBlock  $contentBlock  Bloc vidéo demandé
   * @return StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function stream(Request $request, ContentBlock $contentBlock)
  {
    $contentBlock->load(['chapter.course.program', 'mediaAsset']);

    if ($contentBlock->type !== 'video' || $contentBlock->mediaAsset === null) {
      abort(404);
    }

    $chapter = $contentBlock->chapter;
    $user = $request->user('member');

    if ($chapter === null || ! $this->progressService->canAccess($user, $chapter)) {
      abort(403);
    }

    $disk = Storage::disk($contentBlock->mediaAsset->disk);
    $path = $contentBlock->mediaAsset->path;

    if (! $disk->exists($path)) {
      abort(404);
    }

    return response()->file($disk->path($path), [
      'Content-Type' => $contentBlock->mediaAsset->mime_type ?? 'video/mp4',
      'Accept-Ranges' => 'bytes',
      'Cache-Control' => 'private, max-age=3600',
    ]);
  }
}
