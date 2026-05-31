<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Remise d'un cahier de méditation par un fidèle ECAP.
 */
class EcapMeditationSubmission extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'ecap_meditation_template_id',
    'user_id',
    'enrollment_id',
    'answer_text',
    'file_path',
    'status',
    'moderator_notes',
    'reviewed_by_user_id',
    'submitted_at',
    'reviewed_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'submitted_at' => 'datetime',
      'reviewed_at' => 'datetime',
    ];
  }

  /**
   * Modèle de cahier associé.
   */
  public function template(): BelongsTo
  {
    return $this->belongsTo(EcapMeditationTemplate::class, 'ecap_meditation_template_id');
  }

  /**
   * Fidèle auteur.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Modérateur correcteur.
   */
  public function reviewedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'reviewed_by_user_id');
  }

  /**
   * URL du fichier remis.
   */
  public function getFileUrlAttribute(): ?string
  {
    if (! filled($this->file_path)) {
      return null;
    }

    return asset('storage/'.$this->file_path);
  }
}
