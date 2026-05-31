/**
 * Bandeau de décompte d'accès à un module ECAP.
 *
 * @param {Object} props
 * @param {Object|null} props.countdown Payload serveur (label, urgency, access_open)
 * @param {string} [props.className] Classes additionnelles
 * @returns {JSX.Element|null}
 */
export default function ModuleCountdownBadge({ countdown, className = '' }) {
  if (!countdown?.label) {
    return null;
  }

  const styles = {
    ok: 'border-emerald-200 bg-emerald-50 text-emerald-800',
    warning: 'border-amber-300 bg-amber-50 text-amber-900',
    critical: 'border-red-300 bg-red-50 text-red-800 animate-pulse',
    closed: 'border-phila-gray-200 bg-phila-gray-100 text-phila-gray-600',
  };

  const urgencyClass = styles[countdown.urgency] ?? styles.ok;

  return (
    <div
      className={`flex items-start gap-2 rounded-xl border px-3 py-2 text-sm font-semibold ${urgencyClass} ${className}`}
      role="status"
    >
      <span className="text-lg leading-none" aria-hidden>
        {countdown.urgency === 'closed' ? '🔒' : '⏳'}
      </span>
      <span>{countdown.label}</span>
    </div>
  );
}
