import { router } from '@inertiajs/react';
import AssessmentBadges from './AssessmentBadges';

const statusStyles = {
  completed: {
    badge: 'bg-green-100 text-green-700',
    label: 'Terminé',
    ring: 'ring-green-200',
  },
  in_progress: {
    badge: 'bg-phila-orange-pale text-phila-orange',
    label: 'En cours',
    ring: 'ring-phila-orange/40',
  },
  available: {
    badge: 'bg-phila-orange text-white',
    label: 'Disponible',
    ring: 'ring-phila-orange',
  },
  presentiel_readonly: {
    badge: 'bg-amber-100 text-amber-800',
    label: 'Présentiel',
    ring: 'ring-amber-200',
  },
  locked: {
    badge: 'bg-phila-gray-100 text-phila-gray-400',
    label: 'Verrouillé',
    ring: 'ring-phila-gray-100',
  },
};

const cursusIcons = {
  'connaissez-phila': (
    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
  ),
  metamorpho: (
    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
  ),
  ecap: (
    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
    </svg>
  ),
  gifted: (
    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
    </svg>
  ),
  eyano: (
    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
    </svg>
  ),
};

/**
 * Navigation latérale des 5 cursus PHILA-CE.
 *
 * @param {Object} props
 * @param {Array} props.modules Liste des 5 cursus
 * @param {string} props.activeSlug Slug du cursus actif
 * @returns {JSX.Element}
 */
export default function CursusSidebar({ modules, activeSlug }) {
  const selectCursus = (slug, status) => {
    if (status === 'locked') {
      return;
    }

    router.get('/mon-espace', { cursus: slug }, {
      preserveScroll: true,
    });
  };

  return (
    <nav className="card space-y-2 p-3">
      <p className="px-2 pb-1 text-[10px] font-semibold uppercase tracking-[0.15em] text-phila-gray-600">
        Mes 5 cursus
      </p>

      {modules.map((module) => {
        const styles = statusStyles[module.status] ?? statusStyles.locked;
        const isActive = module.slug === activeSlug;
        const isLocked = module.status === 'locked';

        return (
          <button
            key={module.slug}
            type="button"
            disabled={isLocked}
            onClick={() => selectCursus(module.slug, module.status)}
            className={`w-full rounded-xl border p-3 text-left transition ${
              isActive
                ? `border-phila-orange bg-phila-orange-pale ring-2 ${styles.ring}`
                : isLocked
                  ? 'cursor-not-allowed border-phila-gray-100 bg-phila-gray-50 opacity-60'
                  : 'border-phila-gray-100 hover:border-phila-orange/30 hover:bg-phila-orange-pale/50'
            }`}
          >
            <div className="flex items-start gap-3">
              <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${
                isActive ? 'bg-phila-orange text-white' : isLocked ? 'bg-phila-gray-100 text-phila-gray-400' : 'bg-phila-black text-white'
              }`}>
                {isLocked ? (
                  <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                ) : (
                  cursusIcons[module.slug]
                )}
              </div>

              <div className="min-w-0 flex-1">
                <div className="flex items-center justify-between gap-2">
                  <p className={`text-xs font-bold uppercase tracking-wide ${isLocked ? 'text-phila-gray-400' : 'text-phila-orange'}`}>
                    {module.order}. {module.short_name}
                  </p>
                  <span className={`shrink-0 rounded-full px-2 py-0.5 text-[9px] font-semibold uppercase ${styles.badge}`}>
                    {styles.label}
                  </span>
                </div>
                <p className={`mt-0.5 truncate text-sm font-semibold ${isLocked ? 'text-phila-gray-400' : 'text-phila-black'}`}>
                  {module.name}
                </p>
                <p className="mt-0.5 truncate text-[11px] text-phila-gray-600">{module.subtitle}</p>

                {!isLocked && (module.has_quiz || module.has_tp) && (
                  <AssessmentBadges item={module} muted={false} />
                )}

                {!isLocked && module.stats.total > 0 && (
                  <div className="mt-2">
                    <div className="flex justify-between text-[10px] text-phila-gray-600">
                      <span>{module.stats.completed}/{module.stats.total} étapes</span>
                      <span>{module.progress}%</span>
                    </div>
                    <div className="prog-bar mt-1 h-1">
                      <div className="prog-bar-fill" style={{ width: `${module.progress}%` }} />
                    </div>
                  </div>
                )}
              </div>
            </div>
          </button>
        );
      })}
    </nav>
  );
}
