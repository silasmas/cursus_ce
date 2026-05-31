import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Components/Layout/AppLayout';

/**
 * Historique des quiz passés par le fidèle.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function QuizHistory({ attempts = [], pendingCount = 0, gradedCount = 0 }) {
  return (
    <AppLayout>
      <Head title="Mes quiz — Mon espace" />

      <div className="container-phila max-w-3xl py-10">
        <h1 className="font-display text-2xl font-bold text-phila-black">Mes quiz</h1>
        <p className="mt-1 text-sm text-phila-gray-600">
          Suivez l&apos;état de vos quiz : en attente de correction ou résultats disponibles.
        </p>

        <div className="mt-4 flex flex-wrap gap-3 text-xs">
          <span className="rounded-full bg-blue-100 px-3 py-1 font-semibold text-blue-900">
            {pendingCount} en attente
          </span>
          <span className="rounded-full bg-green-100 px-3 py-1 font-semibold text-green-900">
            {gradedCount} corrigé{gradedCount > 1 ? 's' : ''}
          </span>
        </div>

        {attempts.length === 0 ? (
          <p className="mt-8 rounded-xl border border-dashed border-phila-gray-200 bg-white px-6 py-12 text-center text-sm text-phila-gray-500">
            Vous n&apos;avez pas encore passé de quiz sur la plateforme.
          </p>
        ) : (
          <ul className="mt-8 space-y-4">
            {attempts.map((item) => (
              <li key={item.id} className="rounded-2xl border border-phila-gray-100 bg-white p-4 shadow-sm">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p className="font-semibold text-phila-black">{item.title}</p>
                    <p className="text-xs text-phila-gray-600">
                      {item.module_name}
                      {item.chapter_title && ` · ${item.chapter_title}`}
                    </p>
                    <p className="mt-1 text-[10px] text-phila-gray-400">Soumis le {item.submitted_at}</p>
                  </div>

                  <span
                    className={`rounded-full px-3 py-1 text-[10px] font-bold uppercase ${
                      item.is_pending_grading
                        ? 'bg-blue-100 text-blue-900'
                        : item.passed
                          ? 'bg-green-100 text-green-900'
                          : 'bg-amber-100 text-amber-900'
                    }`}
                  >
                    {item.status_label}
                  </span>
                </div>

                <div className="mt-3 flex flex-wrap items-center justify-between gap-3 border-t border-phila-gray-100 pt-3">
                  {item.is_pending_grading ? (
                    <p className="text-sm text-blue-800">
                      Vos réponses rédigées sont en cours de correction par un enseignant ou un superviseur.
                      {item.score !== null && (
                        <span className="block text-xs text-phila-gray-600">
                          Score provisoire (QCM) : {item.score} %
                        </span>
                      )}
                    </p>
                  ) : (
                    <p className="text-sm text-phila-gray-700">
                      Score final : <strong>{item.score ?? '—'} %</strong>
                    </p>
                  )}

                  {item.result_url && !item.is_pending_grading && (
                    <Link href={item.result_url} className="text-xs font-semibold text-phila-orange hover:underline">
                      Voir le détail →
                    </Link>
                  )}
                </div>
              </li>
            ))}
          </ul>
        )}

        <Link href="/mon-espace" className="mt-8 inline-block text-sm font-semibold text-phila-orange hover:underline">
          ← Retour à mon espace
        </Link>
      </div>
    </AppLayout>
  );
}
