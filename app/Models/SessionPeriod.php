<?php

namespace App\Models;

use App\Enums\SessionPeriodType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Période d'une génération ECAP (cours, TFE, défenses).
 */
class SessionPeriod extends Model
{
  protected $fillable = [
    'academic_session_id',
    'type',
    'name',
    'starts_on',
    'ends_on',
    'sort_order',
    'is_active',
  ];

  protected function casts(): array
  {
    return [
      'type' => SessionPeriodType::class,
      'starts_on' => 'date',
      'ends_on' => 'date',
      'sort_order' => 'integer',
      'is_active' => 'boolean',
    ];
  }

  /**
   * Libellé affiché (personnalisé ou type par défaut).
   * Attribut : display_label (évite le conflit Eloquent avec displayName).
   */
  protected function displayLabel(): Attribute
  {
    return Attribute::get(function (): string {
      if ($this->name !== null && trim($this->name) !== '') {
        return trim($this->name);
      }

      return $this->type?->label() ?? 'Période';
    });
  }

  /**
   * Indique si la période couvre la date du jour.
   */
  public function isActiveNow(): bool
  {
    if (! $this->is_active) {
      return false;
    }

    $today = now()->startOfDay();

    return $today->betweenIncluded(
      $this->starts_on?->startOfDay(),
      $this->ends_on?->endOfDay(),
    );
  }

  public function academicSession(): BelongsTo
  {
    return $this->belongsTo(AcademicSession::class);
  }

  /**
   * Contenus pédagogiques affectés à cette période.
   */
  public function contents(): HasMany
  {
    return $this->hasMany(SessionPeriodContent::class)->orderBy('sort_order');
  }
}
