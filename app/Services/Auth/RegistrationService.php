<?php

namespace App\Services\Auth;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Profile;
use App\Models\Program;
use App\Models\ProgramAccess;
use App\Models\SessionVacation;
use App\Models\User;
use App\Services\ProgramAccess\ProgramAccessStateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Finalise l'inscription multi-étapes d'un fidèle.
 */
class RegistrationService
{
  /**
   * @param  ProgramAccessStateService  $accessStateService  États booléens des accès cursus
   */
  public function __construct(
    private readonly ProgramAccessStateService $accessStateService,
  ) {}

  /**
   * Crée le compte utilisateur et le profil à partir des données d'inscription.
   *
   * @param  array<string, mixed>  $data  Données validées des 5 étapes
   * @return User  Compte créé
   */
  public function complete(array $data): User
  {
    return DB::transaction(function () use ($data) {
      $fullName = trim(implode(' ', array_filter([
        $data['prenom'] ?? '',
        $data['post_nom'] ?? '',
        $data['nom'] ?? '',
      ])));

      $user = User::query()->create([
        'name' => $fullName !== '' ? $fullName : ($data['email'] ?? 'Fidèle'),
        'email' => Str::lower(trim($data['email'])),
        'password' => null,
        'email_verified_at' => now(),
      ]);

      $sessionId = $data['academic_session_id'] ?? AcademicSession::query()
        ->where('is_active', true)
        ->value('id');

      $sessionVacation = ! empty($data['session_vacation_id'])
        ? SessionVacation::query()->find($data['session_vacation_id'])
        : null;

      Profile::query()->create([
        'user_id' => $user->id,
        'academic_session_id' => $sessionId,
        'prenom' => $data['prenom'] ?? null,
        'nom' => $data['nom'] ?? null,
        'post_nom' => $data['post_nom'] ?? null,
        'genre' => $data['genre'] ?? null,
        'etat_civil' => $data['etat_civil'] ?? null,
        'nationalite' => $data['nationalite'] ?? null,
        'nationalite_autre' => $data['nationalite_autre'] ?? null,
        'lieu_naissance' => $data['lieu_naissance'] ?? null,
        'date_naissance' => $data['date_naissance'] ?? null,
        'phone' => $data['phone'] ?? null,
        'profession' => $data['profession'] ?? null,
        'commune_habitation' => $data['commune_habitation'] ?? null,
        'quartier_habitation' => $data['quartier_habitation'] ?? null,
        'adresse_numero_avenue' => $data['adresse_numero_avenue'] ?? null,
        'contact_email' => $data['email'] ?? null,
        'vacation_choice' => $sessionVacation?->name ?? ($data['vacation_choice'] ?? null),
        'session_vacation_id' => $sessionVacation?->id,
        'vacation_autre' => $data['vacation_autre'] ?? null,
        'est_ne_de_nouveau' => (bool) ($data['est_ne_de_nouveau'] ?? false),
        'annee_nouvelle_naissance' => $data['annee_nouvelle_naissance'] ?? null,
        'eglise_acceptation_jesus' => $data['eglise_acceptation_jesus'] ?? null,
        'est_baptise_eau' => (bool) ($data['est_baptise_eau'] ?? false),
        'eglise_bapteme' => $data['eglise_bapteme'] ?? null,
        'est_passe_metamorphoo' => (bool) ($data['est_passe_metamorphoo'] ?? false),
        'mentor_metamorphoo_nom' => $data['mentor_metamorphoo_nom'] ?? null,
        'souhaite_faire_metamorphoo' => (bool) ($data['souhaite_faire_metamorphoo'] ?? false),
        'eglise_attache' => $data['eglise_attache'] ?? null,
        'eglise_attache_autre' => $data['eglise_attache_autre'] ?? null,
        'souhaite_oeuvrer_phila_apres_apollos' => (bool) ($data['souhaite_oeuvrer_phila_apres_apollos'] ?? false),
        'inscription_submitted_at' => now(),
        'locale' => 'fr',
        'accepted_legal_document_id' => $data['accepted_legal_document_id'] ?? null,
        'legal_document_accepted_at' => ! empty($data['accepted_legal_document_id']) ? now() : null,
      ]);

      $studentRole = Role::query()->firstOrCreate(
        ['name' => 'student', 'guard_name' => 'member'],
      );

      $user->assignRole($studentRole);

      $this->createEnrollment($user, $data, $sessionId);
      $this->createProgramAccesses($user, $data, $sessionId);

      return $user;
    });
  }

  /**
   * Inscrit le fidèle au premier cursus et à ECAP si une session est choisie.
   *
   * @param  User  $user  Compte créé
   * @param  array<string, mixed>  $data  Données d'inscription
   * @param  int|null  $sessionId  Identifiant de session académique
   */
  private function createEnrollment(User $user, array $data, ?int $sessionId): void
  {
    $this->enrollInProgram($user, 'connaissez-phila');

    if (! $sessionId) {
      return;
    }

    $session = AcademicSession::query()->with('program')->find($sessionId);

    if ($session?->program?->slug === 'ecap') {
      $isOnline = array_key_exists('ecap_is_online', $data)
        ? (bool) $data['ecap_is_online']
        : true;

      $vacationId = ! $isOnline && ! empty($data['session_vacation_id'])
        ? (int) $data['session_vacation_id']
        : null;

      $this->enrollInProgram($user, 'ecap', $sessionId, $isOnline, $vacationId);
    }
  }

  /**
   * Crée les droits d'accès aux cursus selon l'inscription.
   *
   * Règles simples (M1) :
   * - Toujours créer un accès pour les cursus obligatoires (ou à défaut, Connaissez-vous PHILA).
   * - Si le fidèle déclare avoir déjà suivi un cursus, créer un statut "declared_completed" à valider par admin.
   * - Si le fidèle souhaite suivre un cursus, créer "open" si le programme est ouvert, sinon "pending".
   *
   * @param  array<string, mixed>  $data  Données d'inscription (toutes étapes fusionnées)
   */
  private function createProgramAccesses(User $user, array $data, ?int $sessionId): void
  {
    $programs = Program::query()
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->get();

    $mandatory = $programs->where('is_mandatory', true);

    if ($mandatory->isEmpty()) {
      $mandatory = $programs->where('slug', 'connaissez-phila');
    }

    foreach ($mandatory as $program) {
      $this->upsertProgramAccess($user, $program, $program->is_open ? 'open' : 'pending', 'registration');
    }

    $metamorpho = $programs->firstWhere('slug', 'metamorpho');

    if ($metamorpho) {
      $hasDone = (bool) ($data['est_passe_metamorphoo'] ?? false);
      $wants = (bool) ($data['souhaite_faire_metamorphoo'] ?? false);

      if ($hasDone) {
        $this->upsertProgramAccess($user, $metamorpho, 'declared_completed', 'registration');
      } elseif ($wants) {
        $this->upsertProgramAccess($user, $metamorpho, $metamorpho->is_open ? 'open' : 'pending', 'registration');
      }
    }

    if ($sessionId) {
      $session = AcademicSession::query()->with('program')->find($sessionId);
      $ecap = $session?->program;

      if ($ecap?->slug === 'ecap') {
        $this->upsertProgramAccess($user, $ecap, $ecap->is_open ? 'open' : 'pending', 'session_enrollment');
      }
    }
  }

  /**
   * Crée ou met à jour un accès de cursus.
   */
  private function upsertProgramAccess(User $user, Program $program, string $status, string $source): ProgramAccess
  {
    $access = ProgramAccess::query()->firstOrCreate(
      [
        'user_id' => $user->id,
        'program_id' => $program->id,
      ],
      [
        'source' => $source,
        'is_pending' => true,
      ],
    );

    $access->update(['source' => $source]);

    return match ($status) {
      'open' => $this->accessStateService->setOpen($access, $source),
      'declared_completed' => $this->accessStateService->setNeedsAdminValidation($access, $source),
      'completed' => $this->accessStateService->setCompleted($access, $source),
      'waived' => $this->accessStateService->setWaived($access),
      default => $this->accessStateService->setPending($access),
    };
  }

  /**
   * Crée une inscription au programme identifié par son slug.
   *
   * @param  User  $user  Fidèle
   * @param  string  $programSlug  Slug du cursus
   * @param  int|null  $sessionId  Session académique (ECAP)
   */
  private function enrollInProgram(
    User $user,
    string $programSlug,
    ?int $sessionId = null,
    bool $isOnline = true,
    ?int $sessionVacationId = null,
  ): void {
    $program = \App\Models\Program::query()
      ->where('slug', $programSlug)
      ->where('is_active', true)
      ->first();

    if (! $program) {
      return;
    }

    $course = $program->courses()
      ->where('is_published', true)
      ->orderBy('sort_order')
      ->first();

    $enrollment = Enrollment::query()->firstOrCreate(
      [
        'user_id' => $user->id,
        'program_id' => $program->id,
      ],
      [
        'course_id' => $course?->id,
        'academic_session_id' => $sessionId,
        'status' => 'active',
        'enrolled_at' => now(),
      ],
    );

    if ($programSlug === 'ecap' && $sessionId !== null) {
      $enrollment->update([
        'is_online' => $isOnline,
        'session_vacation_id' => $isOnline ? null : $sessionVacationId,
        'academic_session_id' => $sessionId,
        'online_mode_updated_at' => now(),
      ]);
    }
  }
}
