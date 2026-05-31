import { Skeleton, SkeletonText } from '../UI/Skeleton';

/**
 * Skeleton de chargement pour connexion et inscription.
 *
 * @returns {JSX.Element}
 */
export default function AuthSkeleton() {
  return (
    <div className="min-h-screen bg-phila-gray-50">
      <div className="border-b border-phila-gray-100 bg-white">
        <div className="container-phila flex h-[72px] items-center">
          <Skeleton className="h-10 w-10 rounded-full" />
        </div>
      </div>

      <div className="container-phila flex min-h-[calc(100vh-72px-160px)] items-center justify-center py-12">
        <div className="w-full max-w-md space-y-6">
          <div className="text-center">
            <Skeleton className="mx-auto mb-4 h-16 w-16 rounded-full" />
            <SkeletonText className="mx-auto h-7 w-40" />
            <SkeletonText className="mx-auto mt-2 h-4 w-64" />
          </div>
          <div className="card space-y-5">
            <div className="space-y-2">
              <SkeletonText className="h-4 w-24" />
              <Skeleton className="h-12 w-full rounded-xl" />
            </div>
            <div className="space-y-2">
              <SkeletonText className="h-4 w-32" />
              <Skeleton className="h-12 w-full rounded-xl" />
            </div>
            <Skeleton className="h-12 w-full rounded-full" />
          </div>
        </div>
      </div>
    </div>
  );
}
