import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import PublicLayout from '../../Components/Layout/PublicLayout';
import LoadingButton from '../../Components/UI/LoadingButton';

/**
 * Affiche un bandeau de message contextuel.
 */
function MessageBanner({ type, message }) {
  if (!message) {
    return null;
  }

  const styles = {
    success: 'border-green-200 bg-green-50 text-green-800',
    error: 'border-red-200 bg-red-50 text-red-800',
    info: 'border-blue-200 bg-blue-50 text-blue-800',
    warning: 'border-amber-200 bg-amber-50 text-amber-800',
  };

  const icons = {
    success: '✓',
    error: '!',
    info: 'i',
    warning: '⚠',
  };

  return (
    <div className={`mb-4 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm ${styles[type]}`} role="alert">
      <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/80 text-xs font-bold">
        {icons[type]}
      </span>
      <p>{message}</p>
    </div>
  );
}

/**
 * Indicateur visuel des 2 étapes de connexion OTP.
 */
function LoginStepper({ step }) {
  const steps = [
    { id: 'email', label: 'E-mail' },
    { id: 'otp', label: 'Code OTP' },
  ];

  const currentIndex = step === 'otp' ? 1 : 0;

  return (
    <div className="mb-6 flex items-center justify-center gap-2">
      {steps.map((s, index) => (
        <div key={s.id} className="flex items-center gap-2">
          <div className={`flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold ${
            index <= currentIndex ? 'bg-phila-orange text-white' : 'bg-phila-gray-100 text-phila-gray-400'
          }`}>
            {index < currentIndex ? '✓' : index + 1}
          </div>
          <span className={`text-xs font-medium ${index === currentIndex ? 'text-phila-orange' : 'text-phila-gray-400'}`}>
            {s.label}
          </span>
          {index < steps.length - 1 && (
            <div className={`mx-1 h-0.5 w-8 ${index < currentIndex ? 'bg-phila-orange' : 'bg-phila-gray-100'}`} />
          )}
        </div>
      ))}
    </div>
  );
}

/**
 * Connexion par e-mail avec envoi d'un code OTP — UX guidée.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Login({ email: sessionEmail, step = 'email', otpSentAt }) {
  const { flash, publicRegistration } = usePage().props;
  const registrationOpen = publicRegistration?.is_open === true;
  const [localMessage, setLocalMessage] = useState(null);

  const emailForm = useForm({ email: sessionEmail || '' });
  const otpForm = useForm({ code: '' });
  const resendForm = useForm({});

  useEffect(() => {
    if (sessionEmail) {
      emailForm.setData('email', sessionEmail);
    }
  }, [sessionEmail]);

  useEffect(() => {
    setLocalMessage(null);
  }, [step, flash?.status, flash?.error, flash?.info]);

  const submitEmail = (event) => {
    event.preventDefault();
    setLocalMessage(null);
    emailForm.post('/connexion/otp', {
      onStart: () => setLocalMessage({ type: 'info', text: 'Envoi du code en cours…' }),
      onSuccess: () => setLocalMessage(null),
      onError: () => setLocalMessage({ type: 'error', text: 'Impossible d\'envoyer le code. Vérifiez votre adresse e-mail.' }),
    });
  };

  const submitOtp = (event) => {
    event.preventDefault();
    setLocalMessage(null);
    otpForm.post('/connexion/verifier', {
      onStart: () => setLocalMessage({ type: 'info', text: 'Vérification du code en cours…' }),
      onSuccess: () => setLocalMessage({ type: 'success', text: 'Connexion réussie ! Redirection…' }),
      onError: () => setLocalMessage({
        type: 'error',
        text: 'Code incorrect ou expiré. Vérifiez les 6 chiffres ou demandez un nouveau code.',
      }),
    });
  };

  const resendOtp = () => {
    setLocalMessage(null);
    resendForm.post('/connexion/renvoyer', {
      onStart: () => setLocalMessage({ type: 'info', text: 'Envoi d\'un nouveau code…' }),
      onError: () => setLocalMessage({ type: 'error', text: 'Impossible de renvoyer le code pour le moment.' }),
    });
  };

  const stepDescriptions = {
    email: {
      title: 'Connexion',
      subtitle: 'Étape 1 — Saisissez l\'adresse e-mail de votre compte PHILA-CE.',
    },
    otp: {
      title: 'Vérification',
      subtitle: 'Étape 2 — Entrez le code à 6 chiffres envoyé à votre boîte mail.',
    },
  };

  const current = stepDescriptions[step] ?? stepDescriptions.email;

  return (
    <PublicLayout showAuthLinks={false}>
      <Head title="Connexion" />

      <div className="container-phila flex min-h-[calc(100vh-72px-160px)] items-center justify-center py-12">
        <div className="w-full max-w-md">
          <div className="mb-6 text-center">
            <img src="/images/phila-logo.png" alt="PHILA" className="logo-phila-orange mx-auto mb-4 h-16 w-16 rounded-full" />
            <LoginStepper step={step} />
            <h1 className="font-display text-2xl font-bold">{current.title}</h1>
            <p className="mt-2 text-sm text-phila-gray-600">{current.subtitle}</p>
          </div>

          <MessageBanner type="success" message={flash?.status} />
          <MessageBanner type="error" message={flash?.error} />
          <MessageBanner type="info" message={flash?.info} />
          <MessageBanner type={localMessage?.type === 'error' ? 'error' : localMessage?.type === 'success' ? 'success' : 'info'} message={localMessage?.text} />

          {step === 'email' ? (
            <form onSubmit={submitEmail} className="card space-y-5">
              <div className="rounded-xl bg-phila-orange-pale px-4 py-3 text-sm text-phila-gray-600">
                Nous vous enverrons un <strong>code à 6 chiffres</strong> par e-mail. Aucun mot de passe requis.
              </div>

              <div>
                <label htmlFor="email" className="label-field">Adresse e-mail</label>
                <input
                  id="email"
                  type="email"
                  className="input-field"
                  value={emailForm.data.email}
                  onChange={(e) => emailForm.setData('email', e.target.value)}
                  placeholder="votre@email.com"
                  required
                  autoFocus
                  disabled={emailForm.processing}
                />
                {emailForm.errors.email && (
                  <p className="mt-2 text-sm text-red-600">{emailForm.errors.email}</p>
                )}
              </div>

              <LoadingButton
                type="submit"
                processing={emailForm.processing}
                loadingText="Envoi en cours…"
                className="btn btn-accent w-full"
              >
                Recevoir mon code
              </LoadingButton>
            </form>
          ) : (
            <form onSubmit={submitOtp} className="card space-y-5">
              <div className="rounded-xl border border-phila-orange/20 bg-phila-orange-pale px-4 py-3">
                <p className="text-xs font-semibold uppercase tracking-wide text-phila-orange">Code envoyé à</p>
                <p className="mt-1 font-semibold text-phila-black">{sessionEmail}</p>
                {otpSentAt && (
                  <p className="mt-1 text-xs text-phila-gray-600">
                    Vérifiez votre boîte mail et le dossier spam. Le code expire dans 10 minutes.
                  </p>
                )}
              </div>

              <div>
                <label htmlFor="code" className="label-field">Code à 6 chiffres</label>
                <input
                  id="code"
                  type="text"
                  inputMode="numeric"
                  autoComplete="one-time-code"
                  maxLength={6}
                  className="input-field text-center text-2xl font-bold tracking-[0.5em]"
                  value={otpForm.data.code}
                  onChange={(e) => otpForm.setData('code', e.target.value.replace(/\D/g, ''))}
                  placeholder="000000"
                  required
                  autoFocus
                  disabled={otpForm.processing}
                />
                {otpForm.errors.code && (
                  <p className="mt-2 text-sm text-red-600">{otpForm.errors.code}</p>
                )}
                <p className="mt-2 text-xs text-phila-gray-600">
                  {otpForm.data.code.length}/6 chiffres saisis
                </p>
              </div>

              <LoadingButton
                type="submit"
                processing={otpForm.processing}
                loadingText="Vérification…"
                disabled={otpForm.data.code.length !== 6}
                className="btn btn-accent w-full"
              >
                Se connecter
              </LoadingButton>

              <div className="flex flex-col gap-2 border-t border-phila-gray-100 pt-4">
                <button
                  type="button"
                  onClick={resendOtp}
                  disabled={resendForm.processing}
                  className="text-center text-sm font-medium text-phila-orange hover:underline disabled:opacity-50"
                >
                  {resendForm.processing ? 'Envoi…' : 'Renvoyer un nouveau code'}
                </button>
                <Link
                  href="/connexion/changer-email"
                  className="text-center text-sm text-phila-gray-600 hover:text-phila-black"
                >
                  Utiliser une autre adresse e-mail
                </Link>
              </div>
            </form>
          )}

          {registrationOpen ? (
            <p className="mt-6 text-center text-sm text-phila-gray-600">
              Pas encore inscrit ?{' '}
              <Link href="/inscription" className="font-semibold text-phila-orange underline">
                Créer un compte
              </Link>
            </p>
          ) : (
            <p className="mt-6 text-center text-sm text-phila-gray-600">
              {publicRegistration?.message || 'Les inscriptions sont actuellement fermées.'}
            </p>
          )}
        </div>
      </div>
    </PublicLayout>
  );
}
