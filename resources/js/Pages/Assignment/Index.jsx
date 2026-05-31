import { Head, Link, usePage } from '@inertiajs/react';

/**
 * Liste des TP d'un chapitre (page dédiée).
 */
export default function Index({ chapter, tps }) {
  const { flash } = usePage().props;

  return (
    <div className="min-h-screen bg-phila-gray-50">
      <Head title={`TP — ${chapter.title}`} />
      <div className="container-phila max-w-2xl py-10">
        <Link href={`/mon-espace/cours/${chapter.id}`} className="text-sm text-phila-orange hover:underline">
          ← Retour à l&apos;étape
        </Link>
        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm">{flash.status}</div>
        )}
        <h1 className="mt-6 font-display text-2xl font-bold">Travaux pratiques — {chapter.title}</h1>
        <p className="mt-2 text-sm text-phila-gray-600">Remettez vos TP ; un formateur les validera pour débloquer la suite.</p>
        <div className="mt-6 space-y-4">
          {tps.map((tp) => (
            <div key={tp.id} className="card">
              <p className="font-semibold">{tp.title}</p>
              <p className="text-xs text-phila-gray-600">Statut : {tp.status}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
