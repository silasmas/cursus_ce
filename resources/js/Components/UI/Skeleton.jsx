/**
 * Bloc skeleton animé réutilisable.
 *
 * @param {Object} props
 * @param {string} [props.className] Classes CSS additionnelles
 * @returns {JSX.Element}
 */
export function Skeleton({ className = '' }) {
  return (
    <div
      className={`animate-pulse rounded-lg bg-phila-gray-100 ${className}`}
      aria-hidden="true"
    />
  );
}

/**
 * Ligne skeleton pour texte.
 *
 * @param {Object} props
 * @param {string} [props.className]
 * @returns {JSX.Element}
 */
export function SkeletonText({ className = 'h-4 w-full' }) {
  return <Skeleton className={className} />;
}

/**
 * Cercle skeleton pour avatar ou icône.
 *
 * @param {Object} props
 * @param {string} [props.className]
 * @returns {JSX.Element}
 */
export function SkeletonCircle({ className = 'h-12 w-12' }) {
  return <Skeleton className={`rounded-full ${className}`} />;
}

/**
 * Carte skeleton générique.
 *
 * @param {Object} props
 * @param {string} [props.className]
 * @returns {JSX.Element}
 */
export function SkeletonCard({ className = '' }) {
  return (
    <div className={`card space-y-4 ${className}`}>
      <SkeletonText className="h-5 w-1/3" />
      <SkeletonText className="h-4 w-full" />
      <SkeletonText className="h-4 w-5/6" />
    </div>
  );
}
