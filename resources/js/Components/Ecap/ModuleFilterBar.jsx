/**
 * Filtres par module (scroll horizontal avec marges).
 *
 * @param {Object} props
 * @param {Array} props.courseModules Modules
 * @param {number|null} props.activeModuleId Module actif
 * @param {boolean} props.loading Chargement du fil
 * @param {Function} props.onSelect Callback sélection
 * @returns {JSX.Element}
 */
export default function ModuleFilterBar({ courseModules = [], activeModuleId, loading, onSelect }) {
  return (
    <div className="mt-6 mb-2 px-1">
      <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-phila-gray-500">Filtrer par module</p>
      <div className="-mx-1 overflow-x-auto px-1 pb-2">
        <div className="flex min-w-min gap-2 pr-4">
          <button
            type="button"
            onClick={() => onSelect(null)}
            disabled={loading}
            className={`shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition ${
              !activeModuleId ? 'bg-phila-orange text-white' : 'bg-white text-phila-gray-600 shadow-sm'
            }`}
          >
            Tous les modules
          </button>
          {courseModules.map((module) => (
            <button
              key={module.id}
              type="button"
              onClick={() => onSelect(module.id)}
              disabled={loading}
              className={`shrink-0 rounded-full px-4 py-1.5 text-xs font-semibold transition ${
                activeModuleId === module.id ? 'bg-phila-orange text-white' : 'bg-white text-phila-gray-600 shadow-sm'
              }`}
            >
              {module.tag}
            </button>
          ))}
        </div>
      </div>
      {loading && <p className="text-xs text-phila-gray-500">Mise à jour du fil…</p>}
    </div>
  );
}
