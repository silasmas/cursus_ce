import { Skeleton, SkeletonCard, SkeletonCircle, SkeletonText } from '../UI/Skeleton';

/**
 * Skeleton de chargement pour le portail mentor.
 *
 * @returns {JSX.Element}
 */
export default function MentorSkeleton() {
  return (
    <div className="flex min-h-screen bg-phila-gray-50">
      <aside className="hidden w-64 flex-col bg-phila-black p-5 lg:flex">
        <SkeletonText className="mb-8 h-8 w-40 bg-white/20" />
        {[1, 2].map((i) => (
          <Skeleton key={i} className="mb-2 h-11 w-full rounded-xl bg-white/10" />
        ))}
      </aside>
      <div className="flex-1">
        <div className="hero-gradient">
          <div className="container-phila py-12">
            <SkeletonText className="mb-2 h-4 w-32 bg-white/20" />
            <SkeletonText className="h-9 w-64 bg-white/20" />
          </div>
        </div>
        <div className="container-phila py-10">
          <div className="grid gap-4 sm:grid-cols-2">
            {[1, 2, 3, 4].map((i) => (
              <SkeletonCard key={i} />
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
