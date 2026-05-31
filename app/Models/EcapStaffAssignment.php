<?php

namespace App\Models;

use App\Enums\EcapVacationRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Affectation d'un acteur de vacation ECAP (enseignant, superviseur, modérateur).
 */
class EcapStaffAssignment extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'academic_session_id',
    'session_vacation_id',
    'course_module_id',
    'user_id',
    'role',
    'is_active',
    'notes',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'role' => EcapVacationRole::class,
      'is_active' => 'boolean',
    ];
  }

  /**
   * Session ECAP concernée.
   */
  public function academicSession(): BelongsTo
  {
    return $this->belongsTo(AcademicSession::class);
  }

  /**
   * Vacation présentiel (optionnelle — null = toute la session).
   */
  public function sessionVacation(): BelongsTo
  {
    return $this->belongsTo(SessionVacation::class);
  }

  /**
   * Module de cours ECAP (enseignant / superviseur par module).
   */
  public function courseModule(): BelongsTo
  {
    return $this->belongsTo(CourseModule::class);
  }

  /**
   * Utilisateur affecté au rôle.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
