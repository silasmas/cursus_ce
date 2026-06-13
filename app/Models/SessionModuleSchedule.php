<?php

namespace App\Models;

use Andreia\FilamentRecurrence\Casts\RecurrenceCast;
use App\Enums\CalendarItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrée du calendrier ECAP : module de cours ou activité planifiée dans une période.
 */
class SessionModuleSchedule extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'academic_session_id',
    'item_type',
    'course_module_id',
    'session_period_id',
    'title',
    'description',
    'recurrence',
    'starts_on',
    'ends_on',
    'sort_order',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'item_type' => CalendarItemType::class,
      'recurrence' => RecurrenceCast::class,
      'starts_on' => 'date',
      'ends_on' => 'date',
      'sort_order' => 'integer',
    ];
  }

  /**
   * Libellé affiché (module ou activité).
   */
  public function displayLabel(): string
  {
    if ($this->item_type === CalendarItemType::Activity) {
      return $this->title ?? 'Activité';
    }

    return $this->courseModule?->name ?? $this->title ?? 'Module';
  }

  /**
   * Session parente.
   */
  public function academicSession(): BelongsTo
  {
    return $this->belongsTo(AcademicSession::class);
  }

  /**
   * Module planifié (si type module).
   */
  public function courseModule(): BelongsTo
  {
    return $this->belongsTo(CourseModule::class);
  }

  /**
   * Période ECAP associée (cours, TFE, défenses).
   */
  public function sessionPeriod(): BelongsTo
  {
    return $this->belongsTo(SessionPeriod::class);
  }
}
