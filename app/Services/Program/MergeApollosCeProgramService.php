<?php

namespace App\Services\Program;

use App\Models\Program;
use Illuminate\Support\Facades\DB;

/**
 * Fusionne le programme doublon « apollos-ce » dans le cursus officiel « ecap ».
 */
class MergeApollosCeProgramService
{
  public const ECAP_SLUG = 'ecap';

  public const LEGACY_APOLLOS_SLUG = 'apollos-ce';

  /**
   * Exécute la fusion et supprime le doublon.
   *
   * @return bool True si une fusion a été effectuée
   */
  public function merge(): bool
  {
    $legacy = Program::query()->where('slug', self::LEGACY_APOLLOS_SLUG)->first();

    if ($legacy === null) {
      $this->normalizeEcapProgram();

      return false;
    }

    $ecap = Program::query()->firstOrCreate(
      ['slug' => self::ECAP_SLUG],
      [
        'name' => 'ECAP',
        'description' => 'Apollos CE — École d\'Apolos : formation biblique structurée (cursus ECAP).',
        'sort_order' => 3,
        'is_active' => true,
        'type' => 'cursus',
        'is_open' => true,
      ],
    );

    DB::transaction(function () use ($legacy, $ecap): void {
      $this->reassignCourses($legacy->id, $ecap->id);
      $this->consolidateLegacyCourses($ecap->id);
      $this->reassignEnrollments($legacy->id, $ecap->id);
      $this->reassignProgramAccesses($legacy->id, $ecap->id);
      $this->reassignSimple($legacy->id, $ecap->id);
      $this->reassignProgramSettings($legacy->id, $ecap->id);

      $legacy->delete();
      $this->normalizeEcapProgram();
    });

    return true;
  }

  /**
   * Met à jour le libellé officiel du cursus ECAP.
   */
  private function normalizeEcapProgram(): void
  {
    Program::query()
      ->where('slug', self::ECAP_SLUG)
      ->update([
        'name' => 'ECAP',
        'description' => 'Apollos CE — École d\'Apolos : formation biblique structurée (cursus ECAP).',
        'type' => 'cursus',
        'sort_order' => 3,
      ]);
  }

  /**
   * Réaffecte les cours en évitant les conflits de slug.
   */
  private function reassignCourses(int $fromId, int $toId): void
  {
    $courses = DB::table('courses')->where('program_id', $fromId)->get();

    foreach ($courses as $course) {
      $slug = $course->slug;
      $slugTaken = DB::table('courses')
        ->where('program_id', $toId)
        ->where('slug', $slug)
        ->exists();

      if ($slugTaken) {
        $slug = $slug.'-apollos-legacy';
      }

      DB::table('courses')
        ->where('id', $course->id)
        ->update([
          'program_id' => $toId,
          'slug' => $slug,
        ]);
    }
  }

  /**
   * Fusionne les cours legacy dans le cours canonique ECAP pour éviter les modules dupliqués.
   */
  private function consolidateLegacyCourses(int $ecapProgramId): void
  {
    $canonical = DB::table('courses')
      ->where('program_id', $ecapProgramId)
      ->where('slug', 'fondamentaux-apollos')
      ->first();

    if ($canonical === null) {
      return;
    }

    $legacyCourses = DB::table('courses')
      ->where('program_id', $ecapProgramId)
      ->where('slug', 'like', '%-apollos-legacy')
      ->get();

    foreach ($legacyCourses as $legacyCourse) {
      $legacyModules = DB::table('course_modules')
        ->where('course_id', $legacyCourse->id)
        ->get();

      foreach ($legacyModules as $legacyModule) {
        $targetModule = DB::table('course_modules')
          ->where('course_id', $canonical->id)
          ->where('name', $legacyModule->name)
          ->first();

        if ($targetModule) {
          DB::table('chapters')
            ->where('course_module_id', $legacyModule->id)
            ->update([
              'course_id' => $canonical->id,
              'course_module_id' => $targetModule->id,
            ]);

          DB::table('assessments')
            ->where('course_module_id', $legacyModule->id)
            ->update([
              'course_id' => $canonical->id,
              'course_module_id' => $targetModule->id,
            ]);

          DB::table('course_modules')->where('id', $legacyModule->id)->delete();

          continue;
        }

        DB::table('course_modules')
          ->where('id', $legacyModule->id)
          ->update(['course_id' => $canonical->id]);
      }

      DB::table('chapters')
        ->where('course_id', $legacyCourse->id)
        ->update(['course_id' => $canonical->id]);

      DB::table('courses')->where('id', $legacyCourse->id)->delete();
    }
  }

  /**
   * Réaffecte les inscriptions sans doublon utilisateur.
   */
  private function reassignEnrollments(int $fromId, int $toId): void
  {
    $rows = DB::table('enrollments')->where('program_id', $fromId)->get();

    foreach ($rows as $row) {
      $exists = DB::table('enrollments')
        ->where('user_id', $row->user_id)
        ->where('program_id', $toId)
        ->exists();

      if ($exists) {
        DB::table('enrollments')->where('id', $row->id)->delete();

        continue;
      }

      DB::table('enrollments')
        ->where('id', $row->id)
        ->update(['program_id' => $toId]);
    }
  }

  /**
   * Réaffecte les accès cursus sans doublon utilisateur.
   */
  private function reassignProgramAccesses(int $fromId, int $toId): void
  {
    $rows = DB::table('program_accesses')->where('program_id', $fromId)->get();

    foreach ($rows as $row) {
      $exists = DB::table('program_accesses')
        ->where('user_id', $row->user_id)
        ->where('program_id', $toId)
        ->exists();

      if ($exists) {
        DB::table('program_accesses')->where('id', $row->id)->delete();

        continue;
      }

      DB::table('program_accesses')
        ->where('id', $row->id)
        ->update(['program_id' => $toId]);
    }
  }

  /**
   * Réaffecte les entités liées sans contrainte d'unicité composite.
   */
  private function reassignSimple(int $fromId, int $toId): void
  {
    foreach ([
      'academic_sessions',
      'assessments',
      'certificates',
      'certificate_templates',
      'mentor_assignments',
    ] as $table) {
      DB::table($table)
        ->where('program_id', $fromId)
        ->update(['program_id' => $toId]);
    }
  }

  /**
   * Conserve un seul jeu de paramètres programme.
   */
  private function reassignProgramSettings(int $fromId, int $toId): void
  {
    $targetExists = DB::table('program_settings')->where('program_id', $toId)->exists();

    if ($targetExists) {
      DB::table('program_settings')->where('program_id', $fromId)->delete();

      return;
    }

    DB::table('program_settings')
      ->where('program_id', $fromId)
      ->update(['program_id' => $toId]);
  }
}
