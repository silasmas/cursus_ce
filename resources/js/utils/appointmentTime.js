const MEETING_DURATION_MS = 60 * 60 * 1000;
const SOON_THRESHOLD_MS = 24 * 60 * 60 * 1000;

/**
 * Détermine la phase temporelle d'un rendez-vous.
 *
 * @param {string|Date} scheduledAtIso Date ISO du RDV
 * @returns {'past'|'future'|'soon'|'ongoing'}
 */
export function getAppointmentPhase(scheduledAtIso) {
  const start = new Date(scheduledAtIso).getTime();
  const end = start + MEETING_DURATION_MS;
  const now = Date.now();

  if (now > end) {
    return 'past';
  }

  if (now >= start && now <= end) {
    return 'ongoing';
  }

  if (start - now < SOON_THRESHOLD_MS) {
    return 'soon';
  }

  return 'future';
}

/**
 * Libellé de badge pour la phase du rendez-vous.
 *
 * @param {'past'|'future'|'soon'|'ongoing'} phase Phase du RDV
 * @returns {{ label: string, className: string }}
 */
export function getPhaseBadge(phase) {
  const map = {
    past: { label: 'Passé', className: 'bg-phila-gray-100 text-phila-gray-600' },
    future: { label: 'À venir', className: 'bg-blue-100 text-blue-800' },
    soon: { label: 'Prochainement', className: 'bg-amber-100 text-amber-800' },
    ongoing: { label: 'En cours', className: 'bg-green-100 text-green-800' },
  };

  return map[phase] ?? map.future;
}

/**
 * Formate le temps restant ou écoulé pour un rendez-vous.
 *
 * @param {string|Date} scheduledAtIso Date ISO du RDV
 * @returns {string}
 */
export function formatAppointmentCountdown(scheduledAtIso) {
  const start = new Date(scheduledAtIso).getTime();
  const end = start + MEETING_DURATION_MS;
  const now = Date.now();
  const phase = getAppointmentPhase(scheduledAtIso);

  if (phase === 'past') {
    return 'Terminé';
  }

  if (phase === 'ongoing') {
    const remaining = end - now;

    return `Se termine dans ${formatDuration(remaining)}`;
  }

  const diff = start - now;

  if (diff >= SOON_THRESHOLD_MS) {
    const days = Math.ceil(diff / (24 * 60 * 60 * 1000));

    return days === 1 ? 'Demain' : `Dans ${days} jours`;
  }

  return `Dans ${formatDuration(diff)}`;
}

/**
 * Formate une durée en heures/minutes/secondes.
 *
 * @param {number} ms Millisecondes
 * @returns {string}
 */
function formatDuration(ms) {
  const totalSeconds = Math.max(0, Math.floor(ms / 1000));
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  if (hours > 0) {
    return `${hours}h ${String(minutes).padStart(2, '0')}min`;
  }

  if (minutes > 0) {
    return `${minutes}min ${String(seconds).padStart(2, '0')}s`;
  }

  return `${seconds}s`;
}

/**
 * Affiche date/heure selon le fuseau local du navigateur.
 *
 * @param {string} iso Date ISO
 * @returns {string}
 */
export function formatLocalDateTime(iso) {
  if (!iso) {
    return '';
  }

  return new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(iso));
}

/**
 * Fuseau horaire affichable pour l'utilisateur connecté.
 *
 * @returns {string}
 */
export function userTimezoneLabel() {
  return Intl.DateTimeFormat().resolvedOptions().timeZone;
}

/**
 * Convertit ISO en valeur datetime-local.
 *
 * @param {string} iso Date ISO
 * @returns {string}
 */
export function isoToLocalInput(iso) {
  if (!iso) {
    return '';
  }

  const date = new Date(iso);
  date.setMinutes(date.getMinutes() - date.getTimezoneOffset());

  return date.toISOString().slice(0, 16);
}

/**
 * Convertit une valeur datetime-local en ISO UTC pour l'API.
 *
 * @param {string} localValue Valeur du champ datetime-local
 * @returns {string}
 */
export function localDateTimeToIso(localValue) {
  if (!localValue) {
    return '';
  }

  return new Date(localValue).toISOString();
}
