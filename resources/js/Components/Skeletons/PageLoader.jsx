import AuthSkeleton from './AuthSkeleton';
import CourseSkeleton from './CourseSkeleton';
import DashboardSkeleton from './DashboardSkeleton';
import LandingSkeleton from './LandingSkeleton';
import MentorSkeleton from './MentorSkeleton';

/**
 * Retourne le composant skeleton adapté à l'URL visitée.
 *
 * @param {string} url Chemin de la page cible
 * @returns {React.ComponentType}
 */
export function resolveSkeleton(url) {
  const path = url.split('?')[0];

  if (path.startsWith('/mentor')) {
    return MentorSkeleton;
  }

  if (path.startsWith('/mon-espace/cours')) {
    return CourseSkeleton;
  }

  if (path.startsWith('/mon-espace')) {
    return DashboardSkeleton;
  }

  if (path.startsWith('/connexion') || path.startsWith('/inscription')) {
    return AuthSkeleton;
  }

  return LandingSkeleton;
}

/**
 * Affiche le skeleton de chargement correspondant à la navigation en cours.
 *
 * @param {Object} props
 * @param {string} props.url URL de la page en cours de chargement
 * @returns {JSX.Element}
 */
export default function PageLoader({ url }) {
  const SkeletonComponent = resolveSkeleton(url);

  return (
    <div className="fixed inset-0 z-[100] overflow-y-auto bg-phila-gray-50">
      <div className="fixed inset-x-0 top-0 z-[101] h-1 overflow-hidden bg-phila-gray-100">
        <div className="h-full w-1/3 animate-[shimmer_1s_ease-in-out_infinite] bg-phila-orange" />
      </div>
      <SkeletonComponent />
    </div>
  );
}
