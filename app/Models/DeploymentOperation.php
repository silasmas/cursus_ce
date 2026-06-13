<?php

namespace App\Models;

use App\Enums\DeploymentOperationStatus;
use App\Enums\DeploymentOperationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Journal d'une opération de maintenance exécutée depuis l'admin (migrations, Shield, storage).
 */
class DeploymentOperation extends Model
{
  /**
   * @var array<int, string>
   */
  protected $fillable = [
    'type',
    'status',
    'command',
    'parameters',
    'output',
    'exit_code',
    'executed_by_user_id',
    'started_at',
    'finished_at',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'type' => DeploymentOperationType::class,
      'status' => DeploymentOperationStatus::class,
      'parameters' => 'array',
      'started_at' => 'datetime',
      'finished_at' => 'datetime',
    ];
  }

  /**
   * Administrateur ayant lancé l'opération.
   */
  public function executedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'executed_by_user_id');
  }

  /**
   * Durée d'exécution lisible (ex. « 1,2 s »).
   */
  public function durationLabel(): ?string
  {
    if ($this->started_at === null || $this->finished_at === null) {
      return null;
    }

    $seconds = $this->started_at->diffInMilliseconds($this->finished_at) / 1000;

    return number_format($seconds, 1, ',', ' ').' s';
  }
}
