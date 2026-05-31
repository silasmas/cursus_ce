/**
 * Indicateur visuel des étapes d'inscription.
 *
 * @param {Object} props
 * @param {number} props.current Étape courante
 * @param {number} props.total Nombre total d'étapes
 * @param {Record<number, string>} props.labels Libellés par étape
 * @returns {JSX.Element}
 */
export default function StepIndicator({ current, total, labels }) {
  return (
    <div className="mb-8">
      <div className="mb-3 flex items-center justify-between text-xs font-medium uppercase tracking-wider text-phila-gray-600">
        <span>Étape {current} sur {total}</span>
        <span>{labels[current]}</span>
      </div>
      <div className="flex gap-2">
        {Array.from({ length: total }, (_, index) => {
          const step = index + 1;
          const isActive = step === current;
          const isDone = step < current;

          return (
            <div
              key={step}
              className={`h-1.5 flex-1 rounded-full transition-colors ${
                isDone || isActive ? 'bg-phila-orange' : 'bg-phila-gray-100'
              }`}
            />
          );
        })}
      </div>
    </div>
  );
}
