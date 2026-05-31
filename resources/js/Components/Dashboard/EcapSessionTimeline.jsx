import ModuleCountdownBadge from '../UI/ModuleCountdownBadge';

/**
 * Timeline verticale enrichie du calendrier ECAP (style PHILA-CE).
 *
 * @param {Object} props
 * @param {Object} props.timeline Données timeline
 * @returns {JSX.Element|null}
 */
export default function EcapSessionTimeline({ timeline }) {
  if (!timeline?.has_session) {
    return null;
  }

  const statusStyles = {
    past: {
      dot: 'bg-phila-gray-300 ring-phila-gray-200',
      card: 'border-phila-gray-100 bg-phila-gray-50/90 opacity-90',
      badge: 'bg-phila-gray-100 text-phila-gray-600',
      label: 'Terminé',
      line: 'from-phila-gray-200 to-phila-gray-100',
    },
    current: {
      dot: 'bg-phila-orange ring-phila-orange/35 shadow-[0_0_0_4px_rgba(243,146,0,0.15)]',
      card: 'border-phila-orange/45 bg-gradient-to-br from-phila-orange-pale/50 to-white shadow-md',
      badge: 'bg-phila-orange text-white',
      label: 'En cours',
      line: 'from-phila-orange/40 to-phila-orange/10',
    },
    upcoming: {
      dot: 'bg-white ring-2 ring-phila-orange/50 border border-phila-orange/30',
      card: 'border-phila-gray-100 bg-white shadow-sm',
      badge: 'bg-phila-orange-pale text-phila-orange',
      label: 'À venir',
      line: 'from-phila-orange/20 to-transparent',
    },
  };

  const typeIcons = {
    period: '📆',
    module: '📘',
    activity: '✨',
  };

  return (
    <section className="card overflow-hidden">
      <div className="mb-8 border-b border-phila-gray-100 pb-5">
        <p className="text-xs font-semibold uppercase tracking-[0.15em] text-phila-orange">Calendrier ECAP</p>
        <h2 className="font-display text-2xl font-bold text-phila-black">
          {timeline.session_name ?? 'Parcours de la session'}
        </h2>
        <p className="mt-1 text-sm text-phila-gray-600">Modules, activités et périodes de votre génération.</p>
      </div>

      {timeline.items.length === 0 ? (
        <p className="rounded-xl bg-phila-gray-50 px-4 py-6 text-center text-sm text-phila-gray-600">
          Aucune entrée calendrier publiée pour le moment.
        </p>
      ) : (
        <ol className="relative space-y-0 border-l-[3px] border-phila-orange/20 pl-10">
          {timeline.items.map((item, index) => {
            const style = statusStyles[item.status] ?? statusStyles.upcoming;
            const countdown = item.countdown;

            return (
              <li
                key={item.id}
                className={`relative pb-10 ${index === timeline.items.length - 1 ? 'pb-0' : ''}`}
              >
                <span
                  className={`absolute -left-[2.65rem] top-5 flex h-9 w-9 items-center justify-center rounded-full text-base ring-4 ring-white ${style.dot}`}
                  aria-hidden
                >
                  {typeIcons[item.type] ?? '•'}
                </span>

                <article className={`overflow-hidden rounded-2xl border p-5 transition-shadow ${style.card}`}>
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0 flex-1">
                      <p className="text-[10px] font-bold uppercase tracking-wider text-phila-gray-500">
                        {item.subtitle}
                      </p>
                      <h3 className="mt-1 font-display text-lg font-bold leading-snug text-phila-black">
                        {item.title}
                      </h3>
                    </div>
                    <span
                      className={`shrink-0 rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wide ${style.badge}`}
                    >
                      {style.label}
                    </span>
                  </div>

                  <div className={`mt-3 h-1 w-full rounded-full bg-gradient-to-r ${style.line}`} aria-hidden />

                  <p className="mt-3 flex flex-wrap items-center gap-2 text-sm text-phila-gray-600">
                    <span className="inline-flex items-center gap-1 rounded-md bg-phila-gray-50 px-2 py-0.5 text-xs font-medium">
                      📅 {item.starts_on}
                      {item.ends_on && item.ends_on !== item.starts_on ? ` → ${item.ends_on}` : ''}
                    </span>
                  </p>

                  {countdown && <ModuleCountdownBadge countdown={countdown} className="mt-4" />}

                  {item.description && (
                    <p className="mt-3 text-sm leading-relaxed text-phila-gray-700">{item.description}</p>
                  )}
                </article>
              </li>
            );
          })}
        </ol>
      )}
    </section>
  );
}
