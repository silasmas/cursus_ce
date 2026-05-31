<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Vacation (créneau présentiel) proposée lors de l'inscription à une session ECAP.
 */
class SessionVacation extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'academic_session_id',
    'name',
    'code',
    'time_starts',
    'time_ends',
    'capacity_max',
    'is_active',
    'sort_order',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'capacity_max' => 'integer',
      'is_active' => 'boolean',
      'sort_order' => 'integer',
    ];
  }

  /**
   * Tranche horaire formatée (ex. 08h00 – 12h00).
   */
  public function timeRangeLabel(): ?string
  {
    if ($this->time_starts === null && $this->time_ends === null) {
      return null;
    }

    $start = $this->formatTime($this->time_starts);
    $end = $this->formatTime($this->time_ends);

    if ($start && $end) {
      return "{$start} – {$end}";
    }

    return $start ?? $end;
  }

  /**
   * Session ECAP associée.
   */
  public function academicSession(): BelongsTo
  {
    return $this->belongsTo(AcademicSession::class);
  }

  /**
   * Profils ayant choisi cette vacation.
   */
  public function profiles(): HasMany
  {
    return $this->hasMany(Profile::class);
  }

  /**
   * Inscriptions ECAP liées à cette vacation.
   */
  public function enrollments(): HasMany
  {
    return $this->hasMany(Enrollment::class);
  }

  /**
   * Formate une heure SQL en HHhMM.
   */
  private function formatTime(?string $time): ?string
  {
    if ($time === null || $time === '') {
      return null;
    }

    $parts = explode(':', $time);

    return sprintf('%02dh%02d', (int) $parts[0], (int) ($parts[1] ?? 0));
  }
}
