import { Skeleton, SkeletonCard, SkeletonCircle, SkeletonText } from '../UI/Skeleton';

/**
 * Skeleton de chargement pour la page Mon Espace.
 *
 * @returns {JSX.Element}
 */
export default function DashboardSkeleton() {
  return (
    <div className="min-h-screen bg-phila-gray-50">
      <div className="hero-gradient">
        <div className="container-phila py-12 sm:py-16">
          <SkeletonText className="mb-3 h-3 w-24 bg-white/20" />
          <SkeletonText className="mb-2 h-10 w-72 bg-white/20" />
          <SkeletonText className="h-4 w-96 max-w-full bg-white/20" />
          <div className="mt-8 flex gap-4">
            {[1, 2, 3].map((i) => (
              <Skeleton key={i} className="h-20 w-28 rounded-2xl bg-white/15" />
            ))}
          </div>
        </div>
      </div>

      <div className="container-phila py-10">
        <div className="grid gap-6 lg:grid-cols-[280px_1fr]">
          <aside className="space-y-4">
            <div className="card flex items-center gap-4">
              <SkeletonCircle className="h-14 w-14" />
              <div className="flex-1 space-y-2">
                <SkeletonText className="h-4 w-32" />
                <SkeletonText className="h-3 w-40" />
              </div>
            </div>
            <div className="card space-y-2 p-3">
              {[1, 2, 3].map((i) => (
                <Skeleton key={i} className="h-11 w-full rounded-xl" />
              ))}
            </div>
          </aside>

          <div className="space-y-6">
            <div className="card space-y-5">
              <SkeletonText className="h-6 w-48" />
              {[1, 2, 3, 4].map((i) => (
                <div key={i} className="flex gap-4 rounded-xl border border-phila-gray-100 p-4">
                  <SkeletonCircle className="h-10 w-10 shrink-0" />
                  <div className="flex-1 space-y-2">
                    <SkeletonText className="h-4 w-3/4" />
                    <SkeletonText className="h-3 w-1/2" />
                  </div>
                </div>
              ))}
            </div>
            <SkeletonCard />
          </div>
        </div>
      </div>
    </div>
  );
}
