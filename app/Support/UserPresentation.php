<?php

namespace App\Support;

use App\Models\User;

/**
 * Présentation utilisateur (photo, initiales, nom) pour portail et admin.
 */
class UserPresentation
{
  /**
   * Données d'affichage pour un utilisateur.
   *
   * @return array{name: string, email: string|null, initials: string, avatar_url: string|null}
   */
  public static function for(?User $user): array
  {
    if ($user === null) {
      return [
        'name' => '—',
        'email' => null,
        'initials' => '?',
        'avatar_url' => null,
      ];
    }

    $user->loadMissing('profile', 'mentorProfile');

    $avatarPath = $user->profile?->avatar_path ?? $user->mentorProfile?->avatar_path;
    $version = $user->profile?->avatar_path
      ? ($user->profile->updated_at?->timestamp ?? time())
      : ($user->mentorProfile?->updated_at?->timestamp ?? time());

    return [
      'name' => $user->name,
      'email' => $user->email,
      'initials' => self::initials($user->name),
      'avatar_url' => $avatarPath ? asset('storage/'.$avatarPath).'?v='.$version : null,
    ];
  }

  /**
   * Initiales à partir du nom complet.
   */
  public static function initials(string $name): string
  {
    $parts = array_filter(explode(' ', trim($name)));

    if (count($parts) === 0) {
      return 'U';
    }

    if (count($parts) === 1) {
      return strtoupper(substr($parts[0], 0, 2));
    }

    return strtoupper(substr($parts[0], 0, 1).substr((string) end($parts), 0, 1));
  }
}
