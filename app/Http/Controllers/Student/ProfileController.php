<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EcapStaffAssignment;
use App\Models\Profile;
use App\Models\User;
use App\Services\Ecap\VacationQuestionService;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Profil fidèle/acteur : consultation et mise à jour des informations non uniques.
 */
class ProfileController extends Controller
{
  /**
   * @param  MentorPortalService  $mentorPortalService  Portail mentor / mentoré
   * @param  VacationQuestionService  $vacationQuestionService  Session et profils ECAP
   */
  public function __construct(
    private readonly MentorPortalService $mentorPortalService,
    private readonly VacationQuestionService $vacationQuestionService,
  ) {}

  /**
   * Affiche la page Profil.
   */
  public function show(Request $request): Response
  {
    $user = $request->user('member')->loadMissing('profile');
    $profile = $this->ensureProfile($user);
    $mentorAssignment = $this->mentorPortalService->metamorphoAssignmentForMentee($user);
    $isEcapStaff = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->exists();

    return Inertia::render('Profile/Show', [
      'backUrl' => $isEcapStaff ? '/ecap/acteurs/questions' : '/mon-espace',
      'backLabel' => $isEcapStaff ? '← Espace acteurs ECAP' : '← Mon espace',
      'profile' => [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $profile?->phone,
        'prenom' => $profile?->prenom,
        'post_nom' => $profile?->post_nom,
        'nom' => $profile?->nom,
        'profession' => $profile?->profession,
        'commune_habitation' => $profile?->commune_habitation,
        'quartier_habitation' => $profile?->quartier_habitation,
        'adresse_numero_avenue' => $profile?->adresse_numero_avenue,
        'bio' => $profile?->bio,
        'avatar_url' => $this->avatarPublicUrl($profile),
      ],
      'isMentor' => $user->isMentor(),
      'isMentee' => $mentorAssignment !== null,
      'assignedMentor' => $mentorAssignment
        ? $this->mentorPortalService->mentorProfilePayload($mentorAssignment)
        : null,
    ]);
  }

  /**
   * Affiche le profil public d'un membre ECAP (acteur ou fidèle de la même session).
   */
  public function showMember(Request $request, User $user): Response|RedirectResponse
  {
    $viewer = $request->user('member');

    if ((int) $viewer->id === (int) $user->id) {
      return redirect()->route('profile.show');
    }

    if (! $this->vacationQuestionService->canViewMemberProfile($viewer, $user)) {
      abort(403, 'Vous ne pouvez pas consulter ce profil.');
    }

    $sessionId = $this->vacationQuestionService->studentSession($viewer)?->id
      ?? EcapStaffAssignment::query()
        ->where('user_id', $viewer->id)
        ->where('is_active', true)
        ->value('academic_session_id');

    $isEcapStaff = EcapStaffAssignment::query()
      ->where('user_id', $viewer->id)
      ->where('is_active', true)
      ->exists();

    $referer = $request->headers->get('referer', '');
    $backUrl = str_contains($referer, '/ecap/acteurs/questions')
      ? '/ecap/acteurs/questions'
      : '/mon-espace/ecap/questions';
    $backLabel = str_contains($referer, '/ecap/acteurs/questions')
      ? '← Questions ECAP'
      : '← Questions ECAP';

    $memberPayload = $this->vacationQuestionService->memberProfilePayload($user, $sessionId ? (int) $sessionId : null);

    return Inertia::render('Profile/MemberShow', [
      'member' => $memberPayload,
      'backUrl' => $backUrl,
      'backLabel' => $backLabel,
      'canMessage' => $isEcapStaff && ! $memberPayload['is_ecap_staff'],
      'messageUrl' => '/ecap/acteurs/messages?peer='.$user->id,
    ]);
  }

  /**
   * Met à jour le profil (hors champs uniques).
   */
  public function update(Request $request): RedirectResponse
  {
    $user = $request->user('member');
    $profile = $this->ensureProfile($user);

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'prenom' => ['nullable', 'string', 'max:255'],
      'post_nom' => ['nullable', 'string', 'max:255'],
      'nom' => ['nullable', 'string', 'max:255'],
      'profession' => ['nullable', 'string', 'max:255'],
      'commune_habitation' => ['nullable', 'string', 'max:255'],
      'quartier_habitation' => ['nullable', 'string', 'max:255'],
      'adresse_numero_avenue' => ['nullable', 'string', 'max:255'],
      'bio' => ['nullable', 'string', 'max:1500'],
      'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
      'remove_avatar' => ['nullable', 'boolean'],
    ]);

    $displayName = trim(implode(' ', array_filter([
      $validated['prenom'] ?? $profile->prenom,
      $validated['post_nom'] ?? $profile->post_nom,
      $validated['nom'] ?? $profile->nom,
    ])));

    $user->update([
      'name' => $displayName !== '' ? $displayName : $validated['name'],
    ]);

    $avatarPath = $profile->avatar_path;

    if ($request->boolean('remove_avatar') && $avatarPath) {
      Storage::disk('public')->delete($avatarPath);
      $avatarPath = null;
    }

    if ($request->hasFile('avatar')) {
      if ($avatarPath) {
        Storage::disk('public')->delete($avatarPath);
      }

      $uploaded = $request->file('avatar');
      $extension = strtolower($uploaded->getClientOriginalExtension() ?: $uploaded->extension() ?: 'jpg');
      $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

      if (! in_array($extension, $allowedExtensions, true)) {
        $extension = 'jpg';
      }

      $filename = 'avatar-'.$user->id.'-'.now()->timestamp.'.'.$extension;
      $avatarPath = $uploaded->storeAs('profiles/avatars', $filename, 'public');
    }

    $profile->update([
      'prenom' => $validated['prenom'] ?? null,
      'post_nom' => $validated['post_nom'] ?? null,
      'nom' => $validated['nom'] ?? null,
      'profession' => $validated['profession'] ?? null,
      'commune_habitation' => $validated['commune_habitation'] ?? null,
      'quartier_habitation' => $validated['quartier_habitation'] ?? null,
      'adresse_numero_avenue' => $validated['adresse_numero_avenue'] ?? null,
      'bio' => $validated['bio'] ?? null,
      'avatar_path' => $avatarPath,
    ]);

    $profile->refresh();

    return back()->with([
      'status' => 'Profil mis à jour avec succès.',
      'avatar_url' => $this->avatarPublicUrl($profile),
    ]);
  }

  /**
   * URL publique de l'avatar avec cache-bust.
   */
  private function avatarPublicUrl(?\App\Models\Profile $profile): ?string
  {
    if ($profile?->avatar_path === null || $profile->avatar_path === '') {
      return null;
    }

    $version = $profile->updated_at?->timestamp ?? time();

    return asset('storage/'.$profile->avatar_path).'?v='.$version;
  }

  /**
   * Garantit un enregistrement profil (acteurs créés hors inscription).
   */
  private function ensureProfile(\App\Models\User $user): Profile
  {
    if ($user->profile !== null) {
      return $user->profile;
    }

    return Profile::query()->create([
      'user_id' => $user->id,
      'locale' => 'fr',
    ]);
  }
}
