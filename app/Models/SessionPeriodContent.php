<?php

namespace App\Models;

use App\Enums\PeriodContentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Lien entre une période ECAP et un contenu pédagogique (module, chapitre, évaluation).
 */
class SessionPeriodContent extends Model
{
  protected $fillable = [
    'session_period_id',
    'content_type',
    'content_id',
    'sort_order',
    'label',
  ];

  protected function casts(): array
  {
    return [
      'content_type' => PeriodContentType::class,
      'content_id' => 'integer',
      'sort_order' => 'integer',
    ];
  }

  /**
   * Contenu polymorphe (module, chapitre ou évaluation).
   */
  public function content(): MorphTo
  {
    return $this->morphTo(__FUNCTION__, 'content_type', 'content_id');
  }

  public function sessionPeriod(): BelongsTo
  {
    return $this->belongsTo(SessionPeriod::class);
  }
}
