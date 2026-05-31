/**
 * Avatar enseignant avec initiales si pas de photo.
 *
 * @param {Object} props
 * @param {Object|null} props.instructor Données enseignant
 * @param {string} [props.size='sm'] Taille : sm | md
 * @returns {JSX.Element|null}
 */
export default function InstructorChip({ instructor, size = 'sm' }) {
  if (!instructor?.name) {
    return null;
  }

  const avatarSize = size === 'md' ? 'h-9 w-9 text-xs' : 'h-7 w-7 text-[10px]';

  return (
    <div className="mt-2 flex items-center gap-2">
      {instructor.avatar_url ? (
        <img src={instructor.avatar_url} alt="" className={`${avatarSize} rounded-full object-cover`} />
      ) : (
        <span className={`flex ${avatarSize} items-center justify-center rounded-full bg-phila-black font-bold text-white`}>
          {instructor.initials || instructor.name.charAt(0)}
        </span>
      )}
      <span className="text-xs text-phila-gray-600">
        Enseignant : <strong className="text-phila-black">{instructor.name}</strong>
      </span>
    </div>
  );
}
