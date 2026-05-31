<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Connexion des fidèles par e-mail et code OTP.
 */
class LoginController extends Controller
{
  /**
   * @param  OtpService  $otpService  Service de gestion des OTP
   */
  public function __construct(
    private readonly OtpService $otpService,
  ) {}

  /**
   * Affiche le formulaire de connexion par e-mail.
   */
  public function create(Request $request): Response|RedirectResponse
  {
    if ($request->user('member')) {
      return redirect()->route('dashboard');
    }

    $step = $request->session()->get('login_step', 'email');
    $email = $request->session()->get('login_email');

    if ($step === 'otp' && ! $email) {
      $step = 'email';
      $request->session()->forget('login_step');
    }

    return Inertia::render('Auth/Login', [
      'email' => $email,
      'step' => $step,
      'otpSentAt' => $request->session()->get('login_otp_sent_at'),
    ]);
  }

  /**
   * Envoie un code OTP à l'adresse e-mail indiquée.
   */
  public function sendOtp(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'email' => ['required', 'email'],
    ]);

    $email = strtolower(trim($validated['email']));
    $key = 'login-otp:'.$request->ip().':'.$email;

    if (RateLimiter::tooManyAttempts($key, 3)) {
      throw ValidationException::withMessages([
        'email' => 'Trop de tentatives. Patientez quelques minutes avant de redemander un code.',
      ]);
    }

    RateLimiter::hit($key, 300);

    $result = $this->otpService->send($email, $request->ip());

    if (! $result['sent']) {
      throw ValidationException::withMessages([
        'email' => $result['message'],
      ]);
    }

    $request->session()->put('login_email', $email);
    $request->session()->put('login_step', 'otp');
    $request->session()->put('login_otp_sent_at', now()->toIso8601String());

    return redirect()
      ->route('login')
      ->with('status', 'Un code à 6 chiffres vient d\'être envoyé à '.$email.'. Consultez votre boîte mail (et les spams).');
  }

  /**
   * Vérifie le code OTP et connecte l'utilisateur.
   */
  public function verifyOtp(Request $request): RedirectResponse
  {
    $email = $request->session()->get('login_email');

    if (! $email) {
      return redirect()
        ->route('login')
        ->with('error', 'Votre session a expiré. Veuillez saisir à nouveau votre adresse e-mail.');
    }

    $request->session()->put('login_step', 'otp');

    $validated = $request->validate([
      'code' => ['required', 'string', 'size:6'],
    ], [
      'code.required' => 'Saisissez le code à 6 chiffres reçu par e-mail.',
      'code.size' => 'Le code doit contenir exactement 6 chiffres.',
    ]);

    $user = $this->otpService->verify($email, $validated['code']);

    if (! $user) {
      throw ValidationException::withMessages([
        'code' => 'Code incorrect ou expiré. Vérifiez le code reçu ou demandez-en un nouveau.',
      ]);
    }

    Auth::guard('member')->login($user, remember: true);

    $request->session()->forget(['login_email', 'login_step', 'login_otp_sent_at']);
    $request->session()->regenerate();

    return redirect()
      ->intended(route('dashboard'))
      ->with('status', 'Connexion réussie ! Bienvenue dans votre espace PHILA-CE.');
  }

  /**
   * Réinitialise l'étape OTP pour changer d'adresse e-mail.
   */
  public function resetEmail(Request $request): RedirectResponse
  {
    $request->session()->forget(['login_email', 'login_step', 'login_otp_sent_at']);

    return redirect()
      ->route('login')
      ->with('info', 'Saisissez l\'adresse e-mail de votre compte pour recevoir un nouveau code.');
  }

  /**
   * Renvoie un nouveau code OTP à l'e-mail en session.
   */
  public function resendOtp(Request $request): RedirectResponse
  {
    $email = $request->session()->get('login_email');

    if (! $email) {
      return redirect()
        ->route('login')
        ->with('error', 'Aucune adresse e-mail en cours. Recommencez la connexion.');
    }

    $key = 'login-otp:'.$request->ip().':'.$email;

    if (RateLimiter::tooManyAttempts($key, 3)) {
      return redirect()
        ->route('login')
        ->with('error', 'Trop de demandes. Attendez quelques minutes avant de renvoyer un code.');
    }

    RateLimiter::hit($key, 300);

    $result = $this->otpService->send($email, $request->ip());

    if (! $result['sent']) {
      return redirect()
        ->route('login')
        ->with('error', $result['message']);
    }

    $request->session()->put('login_otp_sent_at', now()->toIso8601String());

    return redirect()
      ->route('login')
      ->with('status', 'Un nouveau code a été envoyé à '.$email.'.');
  }

  /**
   * Déconnecte le fidèle du portail membre sans toucher à la session admin Filament.
   */
  public function destroy(Request $request): RedirectResponse
  {
    Auth::guard('member')->logout();

    $request->session()->regenerateToken();

    return redirect()->route('landing');
  }
}
