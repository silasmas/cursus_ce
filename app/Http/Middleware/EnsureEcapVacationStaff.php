<?php

namespace App\Http\Middleware;

use App\Models\EcapStaffAssignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie que le fidèle connecté est affecté comme acteur de vacation ECAP.
 */
class EnsureEcapVacationStaff
{
  /**
   * @param  Closure(Request): Response  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $user = $request->user('member');

    if ($user === null) {
      return redirect()->route('login');
    }

    $isStaff = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->exists();

    if (! $isStaff) {
      abort(403, 'Accès réservé aux acteurs de vacation ECAP.');
    }

    return $next($request);
  }
}
