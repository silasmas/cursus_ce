<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\SessionVacation;
use App\Models\User;
use App\Services\Auth\RegistrationService;
use App\Services\Legal\LegalDocumentService;
use App\Services\Public\RegistrationAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Inscription multi-étapes des fidèles sur la plateforme PHILA-CE.
 */
class RegisterController extends Controller
{
  /**
   * Nombre total d'étapes du formulaire d'inscription.
   */
  private const int TOTAL_STEPS = 5;

  /**
   * @param  RegistrationService  $registrationService  Service de finalisation d'inscription
   */
  public function __construct(
    private readonly RegistrationService $registrationService,
    private readonly RegistrationAvailabilityService $registrationAvailability,
    private readonly LegalDocumentService $legalDocumentService,
  ) {}

  /**
   * Affiche le formulaire d'inscription à l'étape courante.
   */
  public function create(Request $request): Response|RedirectResponse
  {
    if ($request->user('member')) {
      return redirect()->route('dashboard');
    }

    if ($closed = $this->registrationClosedResponse()) {
      return $closed;
    }

    $step = max(1, min(self::TOTAL_STEPS, (int) $request->query('etape', 1)));
    $data = $request->session()->get('registration', []);

    return Inertia::render('Auth/Register', [
      'step' => $step,
      'totalSteps' => self::TOTAL_STEPS,
      'data' => $data,
      'sessions' => AcademicSession::query()
        ->where('is_active', true)
        ->with('program:id,slug')
        ->orderByDesc('starts_on')
        ->get(['id', 'name', 'code', 'program_id', 'registration_opens_at', 'registration_closes_at', 'is_active'])
        ->filter(fn (AcademicSession $session) => $session->isRegistrationOpen())
        ->map(fn (AcademicSession $session) => [
          'id' => $session->id,
          'name' => $session->name,
          'code' => $session->code,
          'program_slug' => $session->program?->slug,
        ])
        ->values(),
      'sessionVacations' => SessionVacation::query()
        ->where('is_active', true)
        ->whereHas('academicSession', fn ($query) => $query
          ->where('is_active', true)
          ->whereHas('program', fn ($program) => $program->where('slug', 'ecap')))
        ->orderBy('sort_order')
        ->get(['id', 'academic_session_id', 'name', 'code', 'time_starts', 'time_ends'])
        ->map(fn (SessionVacation $vacation) => [
          'id' => $vacation->id,
          'academic_session_id' => $vacation->academic_session_id,
          'name' => $vacation->name,
          'code' => $vacation->code,
          'time_range' => $vacation->timeRangeLabel(),
        ])
        ->values(),
      'legalDocument' => $this->legalDocumentService->registrationPayload(),
      'stepLabels' => [
        1 => 'Identité',
        2 => 'Coordonnées',
        3 => 'Parcours spirituel',
        4 => 'Formation ECAP',
        5 => 'Confirmation',
      ],
    ]);
  }

  /**
   * Valide et enregistre les données d'une étape en session.
   */
  public function storeStep(Request $request, int $step): Response|RedirectResponse
  {
    if (! $this->registrationAvailability->isRegistrationFormOpen()) {
      return redirect()->route('register');
    }

    $step = max(1, min(self::TOTAL_STEPS, $step));
    $rules = $this->rulesForStep($step);
    $validated = $request->validate($rules);

    if ($step === 4 && $this->isEcapSessionId($validated['academic_session_id'] ?? null)) {
      $request->validate([
        'ecap_is_online' => ['required', 'boolean'],
      ]);
      $validated['ecap_is_online'] = $request->boolean('ecap_is_online');

      if (! $validated['ecap_is_online']) {
        $request->validate([
          'session_vacation_id' => [
            'required',
            'integer',
            Rule::exists('session_vacations', 'id')
              ->where('academic_session_id', $validated['academic_session_id'])
              ->where('is_active', true),
          ],
        ]);
        $validated['session_vacation_id'] = (int) $request->input('session_vacation_id');
      } else {
        $validated['session_vacation_id'] = null;
      }
    }

    $existing = $request->session()->get('registration', []);
    $merged = array_merge($existing, $validated);

    if ($step === self::TOTAL_STEPS && $request->boolean('accept_legal_document')) {
      $legalDocument = $this->legalDocumentService->activeRegistrationDocument();
      $merged['accepted_legal_document_id'] = $legalDocument?->id;
    }

    $request->session()->put('registration', $merged);

    if ($step < self::TOTAL_STEPS) {
      return redirect()->route('register', ['etape' => $step + 1]);
    }

    if (User::query()->where('email', $merged['email'] ?? '')->exists()) {
      return redirect()
        ->route('register', ['etape' => 2])
        ->withErrors(['email' => 'Cette adresse e-mail est déjà utilisée. Connectez-vous.']);
    }

    $user = $this->registrationService->complete($merged);

    Auth::guard('member')->login($user, remember: true);
    $request->session()->forget('registration');
    $request->session()->regenerate();

    return redirect()->route('dashboard')->with('status', 'Bienvenue ! Votre inscription est confirmée.');
  }

  /**
   * Retourne les règles de validation pour une étape donnée.
   *
   * @param  int  $step  Numéro de l'étape (1 à 5)
   * @return array<string, mixed>
   */
  private function rulesForStep(int $step): array
  {
    return match ($step) {
      1 => [
        'prenom' => ['required', 'string', 'max:100'],
        'nom' => ['required', 'string', 'max:100'],
        'post_nom' => ['nullable', 'string', 'max:100'],
        'genre' => ['required', Rule::in(['M', 'F'])],
        'date_naissance' => ['required', 'date', 'before:today'],
        'lieu_naissance' => ['nullable', 'string', 'max:150'],
        'nationalite' => ['required', 'string', 'max:100'],
        'nationalite_autre' => ['nullable', 'string', 'max:100'],
        'etat_civil' => ['nullable', 'string', 'max:50'],
      ],
      2 => [
        'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        'phone' => ['required', 'string', 'max:30'],
        'profession' => ['nullable', 'string', 'max:150'],
        'commune_habitation' => ['required', 'string', 'max:150'],
        'quartier_habitation' => ['nullable', 'string', 'max:150'],
        'adresse_numero_avenue' => ['nullable', 'string', 'max:255'],
      ],
      3 => [
        'est_ne_de_nouveau' => ['required', 'boolean'],
        'annee_nouvelle_naissance' => ['nullable', 'integer', 'min:1950', 'max:'.date('Y')],
        'eglise_acceptation_jesus' => ['nullable', 'string', 'max:200'],
        'est_baptise_eau' => ['required', 'boolean'],
        'eglise_bapteme' => ['nullable', 'string', 'max:200'],
        'est_passe_metamorphoo' => ['required', 'boolean'],
        'mentor_metamorphoo_nom' => ['nullable', 'string', 'max:150'],
        'souhaite_faire_metamorphoo' => ['required', 'boolean'],
      ],
      4 => [
        'academic_session_id' => ['nullable', 'exists:academic_sessions,id'],
        'ecap_is_online' => ['nullable', 'boolean'],
        'session_vacation_id' => ['nullable', 'integer'],
        'vacation_choice' => ['nullable', 'string', 'max:100'],
        'vacation_autre' => ['nullable', 'string', 'max:100'],
        'eglise_attache' => ['nullable', 'string', 'max:200'],
        'eglise_attache_autre' => ['nullable', 'string', 'max:200'],
        'souhaite_oeuvrer_phila_apres_apollos' => ['required', 'boolean'],
      ],
      5 => [
        'accept_terms' => ['accepted'],
        'accept_legal_document' => [
          $this->legalDocumentService->activeRegistrationDocument() !== null ? 'accepted' : 'nullable',
        ],
      ],
      default => [],
    };
  }

  /**
   * Affiche la page explicative si les inscriptions sont fermées.
   */
  private function registrationClosedResponse(): ?Response
  {
    if ($this->registrationAvailability->isRegistrationFormOpen()) {
      return null;
    }

    return Inertia::render('Auth/RegistrationClosed', [
      'registration' => $this->registrationAvailability->publicPayload(),
    ]);
  }

  /**
   * Indique si la session choisie est une génération ECAP.
   */
  private function isEcapSessionId(mixed $sessionId): bool
  {
    if ($sessionId === null || $sessionId === '') {
      return false;
    }

    return AcademicSession::query()
      ->whereKey($sessionId)
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->exists();
  }
}
