<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle de cahier de méditation publié par un modérateur ECAP.
 */
class EcapMeditationTemplate extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'academic_session_id',
    'session_vacation_id',
    'course_module_id',
    'created_by_user_id',
    'title',
    'instructions',
    'template_file_path',
    'due_on',
    'is_published',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'due_on' => 'date',
      'is_published' => 'boolean',
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
   * Vacation ciblée (null = toute la session).
   */
  public function sessionVacation(): BelongsTo
  {
    return $this->belongsTo(SessionVacation::class);
  }

  /**
   * Module lié (optionnel).
   */
  public function courseModule(): BelongsTo
  {
    return $this->belongsTo(CourseModule::class);
  }

  /**
   * Modérateur créateur.
   */
  public function createdBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by_user_id');
  }

  /**
   * Remises des fidèles.
   */
  public function submissions(): HasMany
  {
    return $this->hasMany(EcapMeditationSubmission::class);
  }

  /**
   * URL du fichier modèle, si présent.
   */
  public function getTemplateFileUrlAttribute(): ?string
  {
    if (! filled($this->template_file_path)) {
      return null;
    }

    return asset('storage/'.$this->template_file_path);
  }
}
