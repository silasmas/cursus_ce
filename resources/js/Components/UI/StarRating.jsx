/**
 * Notation par étoiles cliquables (1 à 5).
 *
 * @param {Object} props
 * @param {number} props.value Note actuelle
 * @param {Function} props.onChange Callback (note)
 * @param {boolean} [props.disabled=false] Désactivé
 * @returns {JSX.Element}
 */
export default function StarRating({ value, onChange, disabled = false }) {
  return (
    <div className="flex items-center gap-1" role="group" aria-label="Note sur 5 étoiles">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          disabled={disabled}
          onClick={() => onChange(star)}
          className={`text-2xl transition hover:scale-110 disabled:cursor-not-allowed ${
            star <= value ? 'text-phila-orange' : 'text-phila-gray-200'
          }`}
          aria-label={`${star} étoile${star > 1 ? 's' : ''}`}
        >
          ★
        </button>
      ))}
      <span className="ml-2 text-sm text-phila-gray-600">{value}/5</span>
    </div>
  );
}
