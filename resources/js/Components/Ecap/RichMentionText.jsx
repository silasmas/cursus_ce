import { Link } from '@inertiajs/react';

const STAFF_ROLES = new Set(['teacher', 'supervisor', 'moderator']);

/**
 * Résout le lien d'une mention @ utilisateur.
 *
 * @param {Object} user Entrée catalogue
 * @returns {{ href: string|null, navigate: string }}
 */
function resolveUserLink(user) {
  if (STAFF_ROLES.has(user.role) && user.profile_url) {
    return { href: user.profile_url, navigate: 'page' };
  }

  if (user.profile_url && user.role === 'member') {
    return { href: user.profile_url, navigate: 'page' };
  }

  return { href: user.filter_url ?? null, navigate: 'filter' };
}

/**
 * Résout le lien d'un hashtag # module ou chapitre.
 *
 * @param {Object|null} module Module catalogue
 * @param {Object|null} chapter Chapitre catalogue
 * @returns {{ href: string|null, navigate: string }}
 */
function resolveHashLink(module, chapter) {
  if (chapter?.url) {
    return { href: chapter.url, navigate: 'page' };
  }

  if (module?.url) {
    return { href: module.url, navigate: 'page' };
  }

  if (module?.filter_url) {
    return { href: module.filter_url, navigate: 'filter' };
  }

  return { href: null, navigate: 'text' };
}

/**
 * Découpe un texte en segments texte / mention cliquable.
 *
 * @param {string} text Corps du message
 * @param {Object} catalog Catalogue mentionCatalog
 * @returns {Array}
 */
function parseSegments(text, catalog) {
  const pattern = /(@[\wÀ-ÿ_-]+|#[\wÀ-ÿ_-]+)/giu;
  const parts = text.split(pattern).filter((part) => part !== '');

  return parts.map((part, index) => {
    if (part.startsWith('@')) {
      const handle = part.toLowerCase();
      const user = catalog?.users?.find((item) => item.mention.toLowerCase() === handle);

      if (user) {
        const link = resolveUserLink(user);

        return {
          key: `u-${index}-${user.id}`,
          type: 'mention',
          label: part,
          href: link.href,
          navigate: link.navigate,
        };
      }

      return { key: `t-${index}`, type: 'text', label: part };
    }

    if (part.startsWith('#')) {
      const tag = part.toLowerCase();
      const module = catalog?.modules?.find((item) => item.tag.toLowerCase() === tag);
      const chapter = catalog?.chapters?.find((item) => item.tag.toLowerCase() === tag);
      const link = resolveHashLink(module, chapter);

      if (link.href) {
        return {
          key: `h-${index}-${module?.id ?? chapter?.id ?? index}`,
          type: 'mention',
          label: part,
          href: link.href,
          navigate: link.navigate,
        };
      }

      return { key: `h-${index}`, type: 'text', label: part };
    }

    return { key: `p-${index}`, type: 'text', label: part };
  });
}

/**
 * Affiche le texte avec @ et # cliquables.
 *
 * @param {Object} props
 * @param {string} props.text Texte brut
 * @param {Object} props.mentionCatalog Catalogue des mentions
 * @param {Function} [props.onFilterClick] Clic filtre interne (évite rechargement page)
 * @returns {JSX.Element}
 */
export default function RichMentionText({ text, mentionCatalog, onFilterClick }) {
  const segments = parseSegments(text ?? '', mentionCatalog);

  return (
    <span className="whitespace-pre-wrap">
      {segments.map((segment) => {
        if (segment.type === 'mention' && segment.href) {
          if (segment.navigate === 'page') {
            return (
              <Link
                key={segment.key}
                href={segment.href}
                className="font-semibold text-phila-orange hover:underline"
              >
                {segment.label}
              </Link>
            );
          }

          if (onFilterClick && segment.navigate === 'filter') {
            return (
              <button
                key={segment.key}
                type="button"
                className="font-semibold text-phila-orange hover:underline"
                onClick={() => onFilterClick(segment.href)}
              >
                {segment.label}
              </button>
            );
          }

          return (
            <a
              key={segment.key}
              href={segment.href}
              className="font-semibold text-phila-orange hover:underline"
            >
              {segment.label}
            </a>
          );
        }

        return <span key={segment.key}>{segment.label}</span>;
      })}
    </span>
  );
}
