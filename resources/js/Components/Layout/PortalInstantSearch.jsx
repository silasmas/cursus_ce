import { router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

const TYPE_ICONS = {
  cursus: '📚',
  module: '📦',
  chapitre: '📖',
  cours: '🎓',
  prof: '👨‍🏫',
  superviseur: '🛡️',
  moderateur: '✅',
  question: '❓',
  reponse: '💬',
  fidele: '👤',
};

const PLACEHOLDERS = {
  global: 'Rechercher cursus, chapitre, module, prof…',
  ecap_questions: 'Rechercher une question, un prof, une réponse…',
  ecap_staff: 'Rechercher un fidèle (nom, e-mail, téléphone)…',
};

/**
 * Détermine le contexte de recherche selon l'URL.
 *
 * @param {string} path Chemin courant
 * @param {boolean} isEcapStaff Utilisateur acteur ECAP
 * @returns {string}
 */
function resolveSearchContext(path, isEcapStaff) {
  if (path.startsWith('/ecap/acteurs')) {
    return 'ecap_staff';
  }

  if (path.startsWith('/mon-espace/ecap/questions')) {
    return 'ecap_questions';
  }

  if (isEcapStaff && path.startsWith('/mon-espace')) {
    return 'ecap_staff';
  }

  return 'global';
}

/**
 * Barre de recherche instantanée contextuelle.
 *
 * @returns {JSX.Element}
 */
export default function PortalInstantSearch() {
  const { auth } = usePage().props;
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);
  const debounceRef = useRef(null);
  const rootRef = useRef(null);

  const context = useMemo(() => {
    if (typeof window === 'undefined') {
      return 'global';
    }

    return resolveSearchContext(window.location.pathname, auth?.user?.isEcapStaff === true);
  }, [auth?.user?.isEcapStaff]);

  useEffect(() => {
    const handleClick = (event) => {
      if (rootRef.current && !rootRef.current.contains(event.target)) {
        setOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClick);

    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  const fetchResults = useCallback(
    async (term) => {
      if (term.trim().length < 2) {
        setResults([]);
        setLoading(false);

        return;
      }

      setLoading(true);

      try {
        const params = new URLSearchParams({
          q: term.trim(),
          context,
        });

        const response = await fetch(`/mon-espace/recherche?${params.toString()}`, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });

        if (response.ok) {
          const data = await response.json();
          setResults(data.results ?? []);
        }
      } finally {
        setLoading(false);
      }
    },
    [context],
  );

  const handleChange = (event) => {
    const value = event.target.value;
    setQuery(value);
    setOpen(true);

    if (debounceRef.current) {
      clearTimeout(debounceRef.current);
    }

    debounceRef.current = setTimeout(() => {
      fetchResults(value);
    }, 280);
  };

  const selectResult = (item) => {
    setOpen(false);
    setQuery('');
    setResults([]);
    router.visit(item.url);
  };

  const showDropdown = open && (loading || results.length > 0 || query.trim().length >= 2);

  return (
    <div ref={rootRef} className="relative mx-auto w-full max-w-xl flex-1 px-2">
      <div className="relative">
        <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-phila-gray-400">🔍</span>
        <input
          type="search"
          value={query}
          onChange={handleChange}
          onFocus={() => setOpen(true)}
          placeholder={PLACEHOLDERS[context] ?? PLACEHOLDERS.global}
          className="w-full rounded-full border border-phila-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm transition focus:border-phila-orange focus:outline-none focus:ring-2 focus:ring-phila-orange/20"
          autoComplete="off"
        />
      </div>

      {showDropdown && (
        <ul className="absolute left-2 right-2 top-full z-[60] mt-1 max-h-80 overflow-y-auto rounded-2xl border border-phila-gray-100 bg-white py-1 shadow-xl">
          {loading && <li className="px-4 py-3 text-xs text-phila-gray-500">Recherche…</li>}
          {!loading && query.trim().length >= 2 && results.length === 0 && (
            <li className="px-4 py-3 text-xs text-phila-gray-500">Aucun résultat pour « {query} »</li>
          )}
          {!loading &&
            results.map((item, index) => (
              <li key={`${item.type}-${item.url}-${index}`}>
                <button
                  type="button"
                  onClick={() => selectResult(item)}
                  className="flex w-full items-start gap-3 px-4 py-2.5 text-left transition hover:bg-phila-orange-pale"
                >
                  <span className="mt-0.5 text-lg">{TYPE_ICONS[item.type] ?? '•'}</span>
                  <span className="min-w-0 flex-1">
                    <span className="block truncate text-sm font-medium text-phila-black">{item.label}</span>
                    <span className="block truncate text-[10px] text-phila-gray-500">
                      {item.type_label}
                      {item.subtitle ? ` · ${item.subtitle}` : ''}
                    </span>
                  </span>
                </button>
              </li>
            ))}
        </ul>
      )}
    </div>
  );
}
