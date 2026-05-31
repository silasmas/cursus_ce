import { Skeleton, SkeletonText } from '../UI/Skeleton';

/**
 * Skeleton de chargement pour le lecteur de cours.
 *
 * @returns {JSX.Element}
 */
export default function CourseSkeleton() {
  return (
    <div className="min-h-screen bg-phila-gray-50">
      <div className="bg-phila-black px-4 py-4">
        <SkeletonText className="h-4 w-32 bg-white/20" />
        <SkeletonText className="mt-2 h-6 w-64 bg-white/20" />
      </div>
      <Skeleton className="aspect-video w-full rounded-none bg-phila-gray-200" />
      <div className="mx-auto grid max-w-[1600px] gap-0 lg:grid-cols-[1fr_360px]">
        <div className="space-y-4 p-6">
          {[1, 2, 3].map((i) => (
            <div key={i} className="rounded-xl border border-phila-gray-100 p-5 space-y-3">
              <SkeletonText className="h-5 w-1/2" />
              <SkeletonText className="h-4 w-full" />
              <SkeletonText className="h-4 w-4/5" />
            </div>
          ))}
        </div>
        <aside className="hidden border-l border-phila-gray-100 bg-white p-4 lg:block">
          <SkeletonText className="mb-4 h-5 w-24" />
          {[1, 2, 3, 4, 5].map((i) => (
            <Skeleton key={i} className="mb-2 h-12 w-full rounded-lg" />
          ))}
        </aside>
      </div>
    </div>
  );
}
