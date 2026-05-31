<?php

namespace App\Models;

use App\Enums\EcapVacationRole;
use App\Enums\VacationQuestionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Question d'un fidèle sur un module ECAP (fil public, réponses des enseignants).
 */
class VacationQuestion extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'academic_session_id',
    'session_vacation_id',
    'course_module_id',
    'asked_by_user_id',
    'addressed_to_role',
    'addressed_to_user_id',
    'is_addressed_to_all_teachers',
    'subject',
    'body',
    'status',
    'answered_by_user_id',
    'answer_body',
    'answered_at',
    'escalation_notified_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'addressed_to_role' => EcapVacationRole::class,
      'status' => VacationQuestionStatus::class,
      'is_addressed_to_all_teachers' => 'boolean',
      'answered_at' => 'datetime',
      'escalation_notified_at' => 'datetime',
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
   * Vacation présentiel liée (optionnelle).
   */
  public function sessionVacation(): BelongsTo
  {
    return $this->belongsTo(SessionVacation::class);
  }

  /**
   * Module de cours ECAP lié à la question.
   */
  public function courseModule(): BelongsTo
  {
    return $this->belongsTo(CourseModule::class);
  }

  /**
   * Fidèle auteur de la question.
   */
  public function askedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'asked_by_user_id');
  }

  /**
   * Enseignant désigné (@mention), si la question ne vise pas @tous.
   */
  public function addressedToUser(): BelongsTo
  {
    return $this->belongsTo(User::class, 'addressed_to_user_id');
  }

  /**
   * Acteur ayant répondu (première réponse — compatibilité).
   */
  public function answeredBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'answered_by_user_id');
  }

  /**
   * Réponses du fil de discussion.
   */
  public function replies(): HasMany
  {
    return $this->hasMany(VacationQuestionReply::class)->orderBy('created_at');
  }
}
