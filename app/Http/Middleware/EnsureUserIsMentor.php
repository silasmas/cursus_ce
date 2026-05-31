<?php

namespace App\Http\Middleware;

use App\Models\MentorAssignment;
use App\Models\MentorProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restreint l'accès aux utilisateurs mentor (profil ou assignation active).
 */
class EnsureUserIsMentor
{
  /**
   * Vérifie que le fidèle connecté est mentor.
   *
   * @param  Request  $request  Requête HTTP
   * @param  Closure  $next  Suite du pipeline
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user('member');

    if (! $user) {
      abort(403, 'Accès réservé aux mentors PHILA-CE.');
    }

    $isMentor = MentorProfile::query()->where('user_id', $user->id)->exists()
      || MentorAssignment::query()
        ->where('mentor_id', $user->id)
        ->where('status', 'active')
        ->exists();

    if (! $isMentor) {
      abort(403, 'Accès réservé aux mentors PHILA-CE.');
    }

    return $next($request);
  }
}
