/**
 * Sélection multiple de mentorés (assignations actives).
 *
 * @param {Object} props
 * @param {Array} props.mentees Liste { assignment_id, name, program_id?, program_name? }
 * @param {number[]} props.selectedIds Identifiants sélectionnés
 * @param {Function} props.onChange Callback (ids) => void
 * @param {number|null} [props.filterProgramId=null] Limite aux mentorés d'un programme
 * @returns {JSX.Element}
 */
export default function MenteeMultiSelect({
  mentees,
  selectedIds,
  onChange,
  filterProgramId = null,
}) {
  const visible = filterProgramId
    ? mentees.filter((m) => m.program_id === filterProgramId)
    : mentees;

  const toggle = (id) => {
    const current = selectedIds ?? [];
    onChange(
      current.includes(id) ? current.filter((x) => x !== id) : [...current, id],
    );
  };

  const selectAll = () => {
    onChange(visible.map((m) => m.assignment_id));
  };

  const clearAll = () => {
    onChange([]);
  };

  if (visible.length === 0) {
    return (
      <p className="text-sm text-phila-gray-600">
        {filterProgramId
          ? 'Aucun mentoré actif pour ce programme.'
          : 'Aucun mentoré actif.'}
      </p>
    );
  }

  return (
    <div>
      <div className="mb-2 flex flex-wrap gap-2 text-xs">
        <button type="button" onClick={selectAll} className="font-semibold text-phila-orange hover:underline">
          Tout sélectionner
        </button>
        <span className="text-phila-gray-300">|</span>
        <button type="button" onClick={clearAll} className="text-phila-gray-600 hover:underline">
          Tout désélectionner
        </button>
      </div>
      <div className="flex flex-wrap gap-2">
        {visible.map((m) => (
          <label
            key={m.assignment_id}
            className={`cursor-pointer rounded-full border px-3 py-1.5 text-xs font-medium ${
              selectedIds?.includes(m.assignment_id)
                ? 'border-phila-orange bg-phila-orange-pale text-phila-orange'
                : 'border-phila-gray-100'
            }`}
          >
            <input
              type="checkbox"
              className="sr-only"
              checked={selectedIds?.includes(m.assignment_id)}
              onChange={() => toggle(m.assignment_id)}
            />
            {m.name}
            {m.program_name && (
              <span className="ml-1 opacity-70">({m.program_name})</span>
            )}
          </label>
        ))}
      </div>
    </div>
  );
}
