import { Link } from '@inertiajs/react';

const STATUS_CONFIG = {
  upcoming: {
    title: 'Les inscriptions n\'ont pas encore débuté',
    badge: 'Ouverture prochaine',
    tone: 'border-blue-200 bg-blue-50',
    icon: '📅',
  },
  closed: {
    title: 'Les inscriptions sont closes',
    badge: 'Période terminée',
    tone: 'border-amber-200 bg-amber-50',
    icon: '🔒',
  },
  disabled: {
    title: 'Inscriptions indisponibles',
    badge: 'Session inactive',
    tone: 'border-phila-gray-200 bg-phila-gray-50',
    icon: '⏸',
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
 * Modale d'information lorsque les inscriptions ne sont pas ouvertes.
 *
 * @param {Object} props
 * @param {boolean} props.open Modale visible
 * @param {Function} props.onClose Fermeture
 * @param {Object} props.registration Données publicRegistration
 * @returns {JSX.Element|null}
 */
export default function RegistrationInfoModal({ open, onClose, registration }) {
  if (!open || registration?.is_open === true) {
    return null;
  }

  const status = registration?.status ?? 'disabled';
  const config = STATUS_CONFIG[status] ?? STATUS_CONFIG.disabled;
  const message = registration?.message || 'Les inscriptions en ligne ne sont pas disponibles pour le moment.';

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true">
      <button
        type="button"
        className="absolute inset-0 bg-black/50 backdrop-blur-sm"
        aria-label="Fermer"
        onClick={onClose}
      />

      <div className="relative w-full max-w-md rounded-2xl border border-phila-gray-100 bg-white p-6 shadow-xl">
        <button
          type="button"
          onClick={onClose}
          className="absolute right-4 top-4 text-phila-gray-600 hover:text-phila-black"
          aria-label="Fermer la fenêtre"
        >
          ✕
        </button>

        <div className={`mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border text-xl ${config.tone}`}>
          <span aria-hidden>{config.icon}</span>
        </div>

        <p className="text-center text-xs font-semibold uppercase tracking-[0.2em] text-phila-orange">
          ECAP
        </p>
        <h2 className="mt-2 text-center font-display text-xl font-bold text-phila-black">
          {config.title}
        </h2>
        <p className="mt-2 text-center">
          <span className="inline-flex rounded-full bg-phila-black px-3 py-1 text-xs font-semibold text-white">
            {config.badge}
          </span>
        </p>

        <p className="mt-4 text-center text-sm leading-relaxed text-phila-gray-600">
          {message}
        </p>

        {status === 'upcoming' && registration.seconds_until_open > 0 && (
          <p className="mt-3 text-center text-sm font-medium text-phila-black">
            Ouverture dans {formatDuration(registration.seconds_until_open)}
          </p>
        )}

        {(registration.registration_opens_at || registration.registration_closes_at) && (
          <dl className="mt-4 space-y-2 rounded-xl bg-phila-orange-pale/40 px-4 py-3 text-sm">
            {registration.registration_opens_at && (
              <div className="flex justify-between gap-4">
                <dt className="text-phila-gray-600">Ouverture</dt>
                <dd className="font-medium">{registration.registration_opens_at}</dd>
              </div>
            )}
            {registration.registration_closes_at && (
              <div className="flex justify-between gap-4">
                <dt className="text-phila-gray-600">Clôture</dt>
                <dd className="font-medium">{registration.registration_closes_at}</dd>
              </div>
            )}
          </dl>
        )}

        <div className="mt-6 flex flex-col gap-2 sm:flex-row">
          <Link href="/inscription" className="btn btn-accent flex-1 text-center text-sm">
            Voir la page détaillée
          </Link>
          <button type="button" onClick={onClose} className="btn btn-outline flex-1 text-sm">
            Fermer
          </button>
        </div>
      </div>
    </div>
  );
}
