import { Head, Link, usePage } from '@inertiajs/react';

/**
 * Présentation d'un test avant de le démarrer.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Show({ assessment, chapterId, chapterTitle, moduleName, pendingQuestions = false }) {
  const { flash } = usePage().props;
  const timeLabel = assessment.time_limit_label
    ?? (assessment.time_limit_seconds > 0
      ? `${Math.ceil(assessment.time_limit_seconds / 60)} min`
      : 'Illimitée');

  return (
    <div className="min-h-screen bg-phila-gray-50">
      <Head title={assessment.title} />
      <div className="container-phila max-w-lg py-12">
        {flash?.error && (
          <div className="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{flash.error}</div>
        )}
        <div className="card space-y-5">
          <div>
            {moduleName && assessment.is_module_exit_quiz && (
              <p className="text-xs font-semibold uppercase text-phila-orange">Quiz fin de module — {moduleName}</p>
            )}
            {chapterTitle && !assessment.is_module_exit_quiz && (
              <p className="text-xs font-semibold uppercase text-phila-orange">{chapterTitle}</p>
            )}
            <h1 className="font-display text-2xl font-bold">{assessment.title}</h1>
          </div>

          {pendingQuestions || !assessment.is_ready ? (
            <div className="rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-sm text-blue-900">
              <p className="font-semibold">Questions en cours de préparation</p>
              <p className="mt-2">
                Ce quiz n&apos;est pas encore disponible ({assessment.questions_count ?? 0} / {assessment.required_questions ?? '—'} question(s) composée(s)).
                Merci de patienter : vous serez notifié dès que vous pourrez le passer.
              </p>
            </div>
          ) : (
            <>
              {assessment.is_module_exit_quiz && (
                <p className="rounded-xl bg-phila-orange-pale px-4 py-3 text-sm text-phila-gray-700">
                  Quiz obligatoire ECAP : {assessment.required_questions ?? 5} questions, seuil {assessment.passing_score} %.
                  {' '}Durée impartie : <strong>{timeLabel}</strong>.
                  {' '}Le module suivant reste verrouillé tant que vous n&apos;avez pas réussi.
                </p>
              )}
              <ul className="space-y-2 text-sm text-phila-gray-600">
                <li>Seuil de réussite : <strong>{assessment.passing_score}%</strong></li>
                <li>
                  Durée impartie : <strong>{timeLabel}</strong>
                  {assessment.time_limit_seconds > 0 && (
                    <span className="text-phila-gray-500"> — le test se soumet automatiquement à expiration</span>
                  )}
                </li>
                <li>Tentatives restantes : <strong>{assessment.remaining_attempts}</strong></li>
                {assessment.last_score !== null && (
                  <li>Dernier score : <strong>{assessment.last_score}%</strong></li>
                )}
              </ul>
            </>
          )}

          {assessment.passed ? (
            <div className="rounded-xl bg-green-50 px-4 py-3 text-sm text-green-800">
              Vous avez réussi ce test.
            </div>
          ) : pendingQuestions || !assessment.is_ready ? (
            <p className="rounded-xl border border-dashed border-phila-gray-200 bg-phila-gray-50 px-4 py-3 text-center text-sm text-phila-gray-600">
              Le bouton « Commencer » apparaîtra lorsque les questions seront prêtes.
            </p>
          ) : assessment.can_start ? (
            <Link href={`/mon-espace/tests/${assessment.id}/demarrer`} method="post" as="button" className="btn btn-accent w-full">
              Commencer le test
            </Link>
          ) : (
            <p className="text-sm text-red-600">Nombre maximum de tentatives atteint.</p>
          )}

          {chapterId && !assessment.is_module_exit_quiz && (
            <Link href={`/mon-espace/cours/${chapterId}`} className="block text-center text-sm text-phila-orange hover:underline">
              ← Retour à l&apos;étape
            </Link>
          )}
          {assessment.is_module_exit_quiz && (
            <Link href="/mon-espace" className="block text-center text-sm text-phila-orange hover:underline">
              ← Retour à mon espace
            </Link>
          )}
        </div>
      </div>
    </div>
  );
}
