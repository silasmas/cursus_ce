<?php

namespace App\Services\Auth;

use App\Mail\LoginOtpMail;
use App\Models\LoginOtp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Génère, envoie et vérifie les codes OTP de connexion.
 */
class OtpService
{
  /**
   * Durée de validité d'un code OTP en minutes.
   */
  private const int EXPIRY_MINUTES = 10;

  /**
   * Génère et envoie un code OTP à l'adresse e-mail indiquée.
   *
   * @param  string  $email  Adresse e-mail du fidèle
   * @param  string|null  $ipAddress  Adresse IP de la requête
   * @return array{sent: bool, message: string}
   */
  public function send(string $email, ?string $ipAddress = null): array
  {
    $email = Str::lower(trim($email));
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
      return [
        'sent' => false,
        'message' => 'Aucun compte n\'est associé à cette adresse e-mail.',
      ];
    }

    LoginOtp::query()
      ->where('email', $email)
      ->whereNull('used_at')
      ->delete();

    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    LoginOtp::query()->create([
      'email' => $email,
      'code' => $code,
      'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
      'ip_address' => $ipAddress,
    ]);

    Mail::to($email)->send(new LoginOtpMail($code, self::EXPIRY_MINUTES));

    return [
      'sent' => true,
      'message' => 'Un code de connexion a été envoyé à votre adresse e-mail.',
    ];
  }

  /**
   * Vérifie un code OTP et retourne l'utilisateur associé.
   *
   * @param  string  $email  Adresse e-mail
   * @param  string  $code  Code à six chiffres
   * @return User|null  Utilisateur connecté ou null si code invalide
   */
  public function verify(string $email, string $code): ?User
  {
    $email = Str::lower(trim($email));
    $code = trim($code);

    $otp = LoginOtp::query()
      ->where('email', $email)
      ->where('code', $code)
      ->whereNull('used_at')
      ->where('expires_at', '>', now())
      ->latest()
      ->first();

    if (! $otp) {
      return null;
    }

    $otp->update(['used_at' => now()]);

    return User::query()->where('email', $email)->first();
  }
}
