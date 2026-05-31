/**
 * Avatar utilisateur (photo ou initiales).
 *
 * @param {Object} props
 * @param {string|null} [props.avatarUrl] URL de la photo
 * @param {string} [props.name] Nom pour les initiales
 * @param {string} [props.sizeClass] Classes taille (ex. h-9 w-9)
 * @param {string} [props.textClass] Classes texte initiales
 * @param {string} [props.className] Classes additionnelles sur le cercle initiales
 * @returns {JSX.Element}
 */
export default function UserAvatar({
  avatarUrl = null,
  name = 'U',
  sizeClass = 'h-9 w-9',
  textClass = 'text-xs',
  className = '',
}) {
  const initials = (name ?? 'U')
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase() || 'U';

  if (avatarUrl) {
    return (
      <img
        src={avatarUrl}
        alt=""
        className={`${sizeClass} shrink-0 rounded-full object-cover bg-white ring-1 ring-phila-gray-100`}
      />
    );
  }

  return (
    <span
      className={`${sizeClass} flex shrink-0 items-center justify-center rounded-full bg-phila-orange/15 font-bold text-phila-orange ${textClass} ${className}`.trim()}
    >
      {initials}
    </span>
  );
}
