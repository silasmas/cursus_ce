<?php

namespace Database\Seeders;

use App\Enums\CalendarItemType;
use App\Enums\PeriodContentType;
use App\Enums\SessionPeriodType;
use App\Models\AcademicSession;
use App\Models\CourseModule;
use App\Models\Program;
use App\Models\SessionModuleSchedule;
use App\Models\SessionPeriod;
use App\Models\SessionPeriodContent;
use Illuminate\Database\Seeder;

/**
 * Calendrier ECAP session 20 (11 mai – 17 juillet 2026) inspiré du document PHILA.
 */
class EcapSession20CalendarSeeder extends Seeder
{
  /**
   * Alimente périodes, modules et activités pour la session ECAP 2026.
   */
  public function run(): void
  {
    $program = Program::query()->where('slug', 'ecap')->first();

    if ($program === null) {
      return;
    }

    $session = AcademicSession::query()->firstOrCreate(
      ['code' => '2026-V1'],
      [
        'program_id' => $program->id,
        'name' => 'Session ECAP 20 — 2026',
        'generation_number' => 20,
        'starts_on' => '2026-05-11',
        'ends_on' => '2026-07-17',
        'is_active' => true,
      ],
    );

    $session->update([
      'name' => 'Session ECAP 20 — 2026',
      'generation_number' => 20,
      'starts_on' => '2026-05-11',
      'ends_on' => '2026-07-17',
    ]);

    $coursesPeriod = SessionPeriod::query()->updateOrCreate(
      [
        'academic_session_id' => $session->id,
        'type' => SessionPeriodType::Courses,
      ],
      [
        'name' => 'Période des cours (11 mai – 17 juillet 2026)',
        'starts_on' => '2026-05-11',
        'ends_on' => '2026-07-17',
        'sort_order' => 1,
        'is_active' => true,
      ],
    );

    $tfePeriod = SessionPeriod::query()->updateOrCreate(
      [
        'academic_session_id' => $session->id,
        'type' => SessionPeriodType::Tfe,
      ],
      [
        'name' => 'Travaux de fin d\'études',
        'starts_on' => '2026-06-07',
        'ends_on' => '2026-06-14',
        'sort_order' => 2,
        'is_active' => true,
      ],
    );

    SessionPeriod::query()->updateOrCreate(
      [
        'academic_session_id' => $session->id,
        'type' => SessionPeriodType::Defenses,
      ],
      [
        'name' => 'Défenses orales',
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-07-17',
        'sort_order' => 3,
        'is_active' => true,
      ],
    );

    $modules = CourseModule::query()
      ->whereHas('course.program', fn ($query) => $query->where('slug', 'ecap'))
      ->orderBy('sort_order')
      ->get();

    $calendarModuleTitles = [
      1 => 'Chap. 1 — Nouvelle naissance',
      2 => 'Chap. 2 — L\'Église',
      3 => 'Chap. 3 — La méditation',
      4 => 'Chap. 4 — Connaître Dieu',
    ];

    foreach ($modules as $index => $module) {
      $title = $calendarModuleTitles[$index + 1] ?? null;

      if ($title !== null) {
        $module->update(['name' => $title]);
      }
    }

    $moduleWindows = [
      ['title' => 'Chap. 1 — Nouvelle naissance', 'starts' => '2026-05-11', 'ends' => '2026-05-17'],
      ['title' => 'Chap. 2 — L\'Église', 'starts' => '2026-05-18', 'ends' => '2026-06-01'],
      ['title' => 'Chap. 3 — La méditation', 'starts' => '2026-06-02', 'ends' => '2026-06-08'],
      ['title' => 'Chap. 4 — Connaître Dieu', 'starts' => '2026-06-09', 'ends' => '2026-06-15'],
    ];

    foreach ($moduleWindows as $index => $window) {
      $module = $modules->get($index);

      if ($module !== null) {
        SessionModuleSchedule::query()->updateOrCreate(
          [
            'academic_session_id' => $session->id,
            'course_module_id' => $module->id,
            'item_type' => CalendarItemType::Module,
          ],
          [
            'session_period_id' => $coursesPeriod->id,
            'title' => $window['title'],
            'starts_on' => $window['starts'],
            'ends_on' => $window['ends'],
            'sort_order' => $index + 1,
          ],
        );

        SessionPeriodContent::query()->firstOrCreate(
          [
            'session_period_id' => $coursesPeriod->id,
            'content_type' => PeriodContentType::CourseModule,
            'content_id' => $module->id,
          ],
          ['sort_order' => $index + 1],
        );
      } else {
        SessionModuleSchedule::query()->updateOrCreate(
          [
            'academic_session_id' => $session->id,
            'item_type' => CalendarItemType::Module,
            'title' => $window['title'],
          ],
          [
            'session_period_id' => $coursesPeriod->id,
            'starts_on' => $window['starts'],
            'ends_on' => $window['ends'],
            'sort_order' => $index + 1,
          ],
        );
      }
    }

    $activities = [
      ['title' => 'TP 1 — Repentance (page 17)', 'starts' => '2026-05-11', 'ends' => '2026-05-11', 'period' => $coursesPeriod->id],
      ['title' => 'Résolution TP 1 (après 2e culte)', 'starts' => '2026-05-17', 'ends' => '2026-05-17', 'period' => $coursesPeriod->id],
      ['title' => 'Remise TP 1', 'starts' => '2026-05-18', 'ends' => '2026-05-18', 'period' => $coursesPeriod->id],
      ['title' => 'TP 2 — Baptême (page 39)', 'starts' => '2026-05-19', 'ends' => '2026-05-19', 'period' => $coursesPeriod->id],
      ['title' => 'Remise TP 2', 'starts' => '2026-06-01', 'ends' => '2026-06-01', 'period' => $coursesPeriod->id],
      ['title' => 'TP 3 — Inspiration (page 50)', 'starts' => '2026-06-01', 'ends' => '2026-06-01', 'period' => $coursesPeriod->id],
      ['title' => 'Remise TP 3', 'starts' => '2026-06-08', 'ends' => '2026-06-08', 'period' => $coursesPeriod->id],
      ['title' => 'Méditation Éphésiens — observation', 'starts' => '2026-06-09', 'ends' => '2026-06-09', 'period' => $coursesPeriod->id],
      ['title' => 'Méditation Éphésiens ch. 1 (Int. & Ap.)', 'starts' => '2026-06-12', 'ends' => '2026-06-12', 'period' => $coursesPeriod->id],
      ['title' => 'Remise des cahiers de méditation', 'starts' => '2026-06-12', 'ends' => '2026-06-12', 'period' => $coursesPeriod->id],
      ['title' => 'Méditation Éphésiens ch. 2 (Int. & Ap.)', 'starts' => '2026-06-15', 'ends' => '2026-06-15', 'period' => $coursesPeriod->id],
      ['title' => 'Méditation Éphésiens ch. 3', 'starts' => '2026-06-16', 'ends' => '2026-06-16', 'period' => $coursesPeriod->id],
      [
        'title' => 'Travail de fin d\'étude — regroupement des étudiants',
        'starts' => '2026-06-07',
        'ends' => '2026-06-14',
        'period' => $tfePeriod->id,
        'description' => 'Pendant cette période les étudiants se regroupent pour travailler leur sujet.',
      ],
    ];

    foreach ($activities as $sort => $activity) {
      SessionModuleSchedule::query()->updateOrCreate(
        [
          'academic_session_id' => $session->id,
          'item_type' => CalendarItemType::Activity,
          'title' => $activity['title'],
        ],
        [
          'session_period_id' => $activity['period'],
          'description' => $activity['description'] ?? null,
          'starts_on' => $activity['starts'],
          'ends_on' => $activity['ends'],
          'sort_order' => 100 + $sort,
        ],
      );
    }
  }
}
