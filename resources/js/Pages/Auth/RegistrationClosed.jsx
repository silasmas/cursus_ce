import { Head, Link, usePage } from '@inertiajs/react';
import PublicLayout from '../../Components/Layout/PublicLayout';

const STATUS_STYLES = {
  disabled: {
    badge: 'Inscriptions indisponibles',
    title: 'Inscription momentanément indisponible',
    ring: 'border-phila-gray-200 bg-phila-gray-50',
    icon: '⏸',
  },
  upcoming: {
    badge: 'Ouverture prochaine',
    title: 'Les inscriptions n\'ont pas encore débuté',
    ring: 'border-blue-200 bg-blue-50',
    icon: '📅',
  },
  closed: {
    badge: 'Inscriptions closes',
    title: 'Les inscriptions sont closes',
    ring: 'border-amber-200 bg-amber-50',
    icon: '🔒',
  },
};

/**
 * Page affichée lorsque les inscriptions publiques ne sont pas ouvertes.
 *
 * @param {Object} props Props Inertia
 * @param {Object} props.registration État des inscriptions (status, message, dates)
 * @returns {JSX.Element}
 */
export default function RegistrationClosed({ registration: registrationProp }) {
  const { publicRegistration } = usePage().props;
  const registration = registrationProp ?? publicRegistration ?? {};
  const status = registration?.status ?? 'disabled';
  const style = STATUS_STYLES[status] ?? STATUS_STYLES.disabled;

  return (
    <PublicLayout>
      <Head title="Inscriptions — PHILA-CE" />

      <div className="container-phila flex min-h-[70vh] items-center justify-center py-24">
        <div className="w-full max-w-lg rounded-2xl border border-phila-gray-100 bg-white p-8 shadow-sm">
          <div className={`mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full text-2xl ${style.ring} border`}>
            <span aria-hidden>{style.icon}</span>
          </div>

          <p className="text-center text-xs font-semibold uppercase tracking-[0.2em] text-phila-orange">
            ECAP
            {registration?.session_name ? ` · ${registration.session_name}` : ''}
          </p>
          <h1 className="mt-2 text-center font-display text-2xl font-bold text-phila-black">
            {style.title}
          </h1>

          <p className="mt-2 text-center">
            <span className="inline-flex rounded-full bg-phila-black px-3 py-1 text-xs font-semibold text-white">
              {style.badge}
            </span>
          </p>

          <p className="mt-6 text-center text-sm leading-relaxed text-phila-gray-600">
            {registration?.message || 'Les inscriptions en ligne ne sont pas disponibles pour le moment. Contactez l\'administration PHILA si vous avez des questions.'}
          </p>

          {(registration?.registration_opens_at || registration?.registration_closes_at) && (
            <dl className="mt-6 space-y-2 rounded-xl bg-phila-orange-pale/50 px-4 py-4 text-sm">
              {registration.registration_opens_at && (
                <div className="flex justify-between gap-4">
                  <dt className="text-phila-gray-600">Ouverture</dt>
                  <dd className="font-medium text-phila-black">{registration.registration_opens_at}</dd>
                </div>
              )}
              {registration.registration_closes_at && (
                <div className="flex justify-between gap-4">
                  <dt className="text-phila-gray-600">Clôture</dt>
                  <dd className="font-medium text-phila-black">{registration.registration_closes_at}</dd>
                </div>
              )}
            </dl>
          )}

          {status === 'upcoming' && (
            <p className="mt-4 text-center text-xs text-phila-gray-600">
              Revenez à partir de la date d&apos;ouverture pour créer votre compte fidèle.
            </p>
          )}

          <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <Link href="/" className="btn btn-outline px-6 text-center">
              Retour à l&apos;accueil
            </Link>
            <Link href="/connexion" className="btn btn-accent px-6 text-center">
              Se connecter
            </Link>
          </div>
        </div>
      </div>
    </PublicLayout>
  );
}
