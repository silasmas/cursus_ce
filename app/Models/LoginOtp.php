<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Code OTP à usage unique pour la connexion par e-mail.
 */
class LoginOtp extends Model
{
  protected $fillable = [
    'email',
    'code',
    'expires_at',
    'used_at',
    'ip_address',
  ];

  /**
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'expires_at' => 'datetime',
      'used_at' => 'datetime',
    ];
  }

  /**
   * Indique si le code OTP est encore valide.
   */
  public function isValid(): bool
  {
    return $this->used_at === null && $this->expires_at->isFuture();
  }
}
