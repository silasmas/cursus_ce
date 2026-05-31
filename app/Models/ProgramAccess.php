<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Accès d'un utilisateur à un cursus (indicateurs booléens + validation admin).
 */
class ProgramAccess extends Model
{
  /**
   * Champs assignables.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id',
    'program_id',
    'is_pending',
    'is_open',
    'is_completed',
    'is_waived',
    'needs_admin_validation',
    'source',
    'validated_by_user_id',
    'validated_at',
  ];

  /**
   * Casts des attributs.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'is_pending' => 'boolean',
      'is_open' => 'boolean',
      'is_completed' => 'boolean',
      'is_waived' => 'boolean',
      'needs_admin_validation' => 'boolean',
      'validated_at' => 'datetime',
    ];
  }

  /**
   * Utilisateur concerné.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Cursus concerné.
   */
  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class);
  }

  /**
   * Administrateur ayant validé.
   */
  public function validatedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'validated_by_user_id');
  }
}
