<?php

namespace App\Services\Student;

use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Gère la remise et le suivi des travaux pratiques (TP).
 */
class AssignmentSubmissionService
{
  /**
   * Soumet un TP (texte et/ou fichier).
   */
  public function submit(
    User $user,
    Assessment $assessment,
    ?string $answerText,
    ?UploadedFile $file = null,
  ): AssignmentSubmission {
    $latest = AssignmentSubmission::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $user->id)
      ->latest('submitted_at')
      ->first();

    if ($latest && $latest->status === SubmissionStatus::Pending->value) {
      throw new \RuntimeException('Un TP est déjà en attente de validation.');
    }

    if ($latest && $latest->status === SubmissionStatus::Approved->value) {
      throw new \RuntimeException('Ce TP est déjà validé.');
    }

    $filePath = null;

    if ($file) {
      $filePath = $file->store('assignments/'.$user->id, 'public');
    }

    $enrollment = $this->resolveEnrollment($user, $assessment);
    $version = ($latest?->version ?? 0) + 1;

    return AssignmentSubmission::query()->create([
      'assessment_id' => $assessment->id,
      'user_id' => $user->id,
      'enrollment_id' => $enrollment?->id,
      'version' => $version,
      'file_path' => $filePath,
      'answer_text' => $answerText,
      'submitted_at' => now(),
      'status' => SubmissionStatus::Pending->value,
      'mentor_status' => 'pending',
      'visible_to_mentee' => true,
      'admin_publication_status' => 'published',
    ]);
  }

  /**
   * Valide ou refuse un TP (formateur / admin).
   */
  public function grade(
    AssignmentSubmission $submission,
    User $grader,
    SubmissionStatus $status,
    ?float $grade = null,
    ?string $notes = null,
  ): AssignmentSubmission {
    $submission->update([
      'status' => $status->value,
      'grade' => $grade,
      'grader_notes' => $notes,
      'grader_id' => $grader->id,
      'graded_at' => now(),
    ]);

    return $submission->fresh();
  }

  /**
   * URL publique du fichier remis.
   */
  public function fileUrl(AssignmentSubmission $submission): ?string
  {
    if (! $submission->file_path) {
      return null;
    }

    return Storage::disk('public')->url($submission->file_path);
  }

  /**
   * Résout l'inscription du fidèle.
   */
  private function resolveEnrollment(User $user, Assessment $assessment): ?Enrollment
  {
    if (! $assessment->program_id) {
      return null;
    }

    return Enrollment::query()->firstOrCreate(
      [
        'user_id' => $user->id,
        'program_id' => $assessment->program_id,
      ],
      [
        'status' => 'active',
        'enrolled_at' => now(),
      ],
    );
  }
}
