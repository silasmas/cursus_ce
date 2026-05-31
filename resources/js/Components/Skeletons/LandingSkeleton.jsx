import { Skeleton, SkeletonText } from '../UI/Skeleton';

/**
 * Skeleton de chargement pour les pages publiques (landing).
 *
 * @returns {JSX.Element}
 */
export default function LandingSkeleton() {
  return (
    <div className="min-h-screen bg-phila-gray-50">
      <div className="border-b border-phila-gray-100 bg-white">
        <div className="container-phila flex h-[72px] items-center justify-between">
          <div className="flex items-center gap-3">
            <Skeleton className="h-10 w-10 rounded-full" />
            <SkeletonText className="h-4 w-24" />
          </div>
          <div className="flex gap-2">
            <Skeleton className="h-10 w-24 rounded-full" />
            <Skeleton className="h-10 w-28 rounded-full" />
          </div>
        </div>
      </div>

      <div className="hero-gradient">
        <div className="container-phila py-24 text-center">
          <Skeleton className="mx-auto mb-8 h-24 w-24 rounded-full bg-white/15" />
          <SkeletonText className="mx-auto mb-4 h-3 w-40 bg-white/20" />
          <SkeletonText className="mx-auto mb-3 h-12 w-96 max-w-full bg-white/20" />
          <SkeletonText className="mx-auto h-4 w-[32rem] max-w-full bg-white/20" />
          <div className="mx-auto mt-10 flex justify-center gap-4">
            <Skeleton className="h-12 w-52 rounded-full bg-white/20" />
            <Skeleton className="h-12 w-44 rounded-full bg-white/15" />
          </div>
        </div>
      </div>

      <div className="container-phila py-20">
        <div className="mx-auto mb-14 max-w-2xl space-y-3 text-center">
          <SkeletonText className="mx-auto h-3 w-28" />
          <SkeletonText className="mx-auto h-8 w-80 max-w-full" />
          <SkeletonText className="mx-auto h-4 w-full" />
        </div>
        <div className="grid gap-6 sm:grid-cols-2">
          {[1, 2, 3, 4].map((i) => (
            <div key={i} className="card space-y-3">
              <Skeleton className="h-10 w-10 rounded-full" />
              <SkeletonText className="h-5 w-2/3" />
              <SkeletonText className="h-4 w-full" />
              <SkeletonText className="h-4 w-5/6" />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
