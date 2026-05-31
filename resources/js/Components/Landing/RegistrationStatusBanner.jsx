import { Link } from '@inertiajs/react';

const STATUS_CONFIG = {
  upcoming: {
    title: 'Inscriptions pas encore ouvertes',
    badge: 'Ouverture prochaine',
    cta: 'Voir les dates d\'ouverture',
    tone: 'border-blue-300/40 bg-blue-500/15 text-blue-50',
  },
  closed: {
    title: 'Inscriptions closes',
    badge: 'Période terminée',
    cta: 'En savoir plus',
    tone: 'border-amber-300/40 bg-amber-500/10 text-amber-100',
  },
  disabled: {
    title: 'Inscriptions indisponibles',
    badge: 'Session inactive',
    cta: 'En savoir plus',
    tone: 'border-white/20 bg-white/10 text-white/80',
  },
};

/**
 * Formate un décompte en jours et heures.
 *
 * @param {number} totalSeconds Secondes restantes
 * @returns {string}
 */
function formatDuration(totalSeconds) {
  const days = Math.floor(totalSeconds / 86400);
  const hours = Math.floor((totalSeconds % 86400) / 3600);

  if (days > 0) {
    return `${days} jour${days > 1 ? 's' : ''} et ${hours} h`;
  }

  return `${hours} h`;
}

/**
 * Bandeau d'état des inscriptions ECAP sur la page d'accueil.
 *
 * @param {Object} props
 * @param {Object} props.registration Données d'inscription publiques
 * @returns {JSX.Element|null}
 */
export default function RegistrationStatusBanner({ registration }) {
  if (!registration || registration.is_open) {
    return null;
  }

  const config = STATUS_CONFIG[registration.status] ?? STATUS_CONFIG.disabled;

  return (
    <section className="border-b border-white/10 bg-phila-black/20 py-8">
      <div className="container-phila">
        <div className={`mx-auto max-w-3xl rounded-2xl border px-6 py-6 text-center ${config.tone}`}>
          <p className="text-xs font-semibold uppercase tracking-[0.2em] opacity-80">
            ECAP
            {registration.session_name ? ` · ${registration.session_name}` : ''}
          </p>
          <h2 className="mt-2 font-display text-xl font-bold text-white">{config.title}</h2>
          <p className="mt-1 text-xs font-semibold uppercase tracking-wide opacity-90">{config.badge}</p>
          <p className="mx-auto mt-4 max-w-xl text-sm leading-relaxed">{registration.message}</p>

          {registration.status === 'upcoming' && registration.seconds_until_open != null && registration.seconds_until_open > 0 && (
            <p className="mt-3 text-sm font-medium">
              Ouverture dans {formatDuration(registration.seconds_until_open)}
            </p>
          )}

          {(registration.registration_opens_at || registration.registration_closes_at) && (
            <dl className="mx-auto mt-4 grid max-w-md gap-2 text-sm sm:grid-cols-2">
              {registration.registration_opens_at && (
                <div className="rounded-lg bg-black/20 px-3 py-2">
                  <dt className="text-xs opacity-70">Ouverture</dt>
                  <dd className="font-medium">{registration.registration_opens_at}</dd>
                </div>
              )}
              {registration.registration_closes_at && (
                <div className="rounded-lg bg-black/20 px-3 py-2">
                  <dt className="text-xs opacity-70">Clôture</dt>
                  <dd className="font-medium">{registration.registration_closes_at}</dd>
                </div>
              )}
            </dl>
          )}

          <Link href="/inscription" className="btn btn-accent mt-6 inline-flex px-6">
            {config.cta}
          </Link>
        </div>
      </div>
    </section>
  );
}
