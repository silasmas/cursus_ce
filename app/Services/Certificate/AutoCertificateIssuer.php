<?php

namespace App\Services\Certificate;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

/**
 * Délivre automatiquement les certificats aux fidèles ayant terminé leur cursus.
 */
class AutoCertificateIssuer
{
  /**
   * Traite toutes les inscriptions éligibles sans certificat.
   *
   * @return int Nombre de certificats générés
   */
  public function issueForEligibleEnrollments(): int
  {
    $issued = 0;

    foreach ($this->eligibleEnrollments() as $enrollment) {
      if (! $this->isEnrollmentCompleted($enrollment)) {
        continue;
      }

      if ($this->alreadyCertified($enrollment)) {
        continue;
      }

      $templateId = $this->resolveTemplateId($enrollment->program_id);

      Certificate::query()->create([
        'user_id' => $enrollment->user_id,
        'program_id' => $enrollment->program_id,
        'academic_session_id' => $enrollment->academic_session_id,
        'enrollment_id' => $enrollment->id,
        'certificate_template_id' => $templateId,
        'number' => $this->generateCertificateNumber($enrollment),
        'issued_at' => now(),
      ]);

      if (! $enrollment->completed_at) {
        $enrollment->forceFill(['completed_at' => now()])->save();
      }

      $issued++;
    }

    return $issued;
  }

  /**
   * Liste des inscriptions à contrôler.
   *
   * @return Collection<int, Enrollment>
   */
  private function eligibleEnrollments(): Collection
  {
    return Enrollment::query()
      ->whereIn('status', ['active', 'completed'])
      ->whereHas('program.courses.chapters', fn ($query) => $query->where('is_published', true))
      ->with(['program.courses.chapters', 'chapterProgress'])
      ->get();
  }

  /**
   * Vérifie qu'un certificat existe déjà pour cette inscription.
   */
  private function alreadyCertified(Enrollment $enrollment): bool
  {
    return Certificate::query()
      ->where('enrollment_id', $enrollment->id)
      ->exists();
  }

  /**
   * Vérifie que toutes les étapes publiées du cursus sont terminées.
   */
  private function isEnrollmentCompleted(Enrollment $enrollment): bool
  {
    $publishedChapterIds = $enrollment->program?->courses
      ?->flatMap(fn ($course) => $course->chapters->where('is_published', true)->pluck('id'))
      ->unique()
      ->values();

    if (! $publishedChapterIds || $publishedChapterIds->isEmpty()) {
      return false;
    }

    $completedCount = $enrollment->chapterProgress
      ->whereIn('chapter_id', $publishedChapterIds)
      ->whereNotNull('completed_at')
      ->unique('chapter_id')
      ->count();

    return $completedCount >= $publishedChapterIds->count();
  }

  /**
   * Sélectionne le template par défaut du cursus.
   */
  private function resolveTemplateId(int $programId): ?int
  {
    $default = CertificateTemplate::query()
      ->where('program_id', $programId)
      ->where('is_default', true)
      ->value('id');

    if ($default) {
      return (int) $default;
    }

    $fallback = CertificateTemplate::query()
      ->where('program_id', $programId)
      ->orderBy('id')
      ->value('id');

    return $fallback ? (int) $fallback : null;
  }

  /**
   * Génère un numéro lisible de certificat.
   */
  private function generateCertificateNumber(Enrollment $enrollment): string
  {
    $count = Certificate::query()
      ->where('program_id', $enrollment->program_id)
      ->count() + 1;

    return sprintf('PHILA-%s-%05d', now()->format('Y'), $count);
  }
}

