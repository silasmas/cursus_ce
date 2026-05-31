import { Head, Link, usePage } from '@inertiajs/react';

/**
 * Résultats d'un test avec correction détaillée et liens de révision.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Result(props) {
  const pageProps = usePage().props;
  const {
    status,
    is_pending_grading: isPendingGradingProp,
    isPendingGrading: isPendingGradingCamel,
    passed,
    score,
    passing_score: passingScore,
    is_module_exit_quiz: isModuleExitQuiz,
    assessment,
    module_name: moduleName,
    graded_by_name: gradedByNameSnake,
    gradedByName: gradedByNameCamel,
    graded_at: gradedAtSnake,
    gradedAt: gradedAtCamel,
    submitted_at: submittedAtSnake,
    submittedAt: submittedAtCamel,
    staff_comments: staffCommentsSnake,
    staffComments: staffCommentsCamel,
    history_url: historyUrlSnake,
    historyUrl: historyUrlCamel,
    questions = [],
    reviews = [],
  } = { ...pageProps, ...props };

  const isPendingGrading = isPendingGradingProp ?? isPendingGradingCamel ?? false;
  const gradedByName = gradedByNameSnake ?? gradedByNameCamel;
  const gradedAt = gradedAtSnake ?? gradedAtCamel;
  const submittedAt = submittedAtSnake ?? submittedAtCamel;
  const staffComments = staffCommentsSnake ?? staffCommentsCamel ?? [];
  const historyUrl = historyUrlSnake ?? historyUrlCamel ?? '/mon-espace/mes-quiz';

  const mcqQuestions = questions.filter((item) => item.type === 'mcq');
  const writtenQuestions = questions.filter((item) => item.type === 'written');
  const pending = isPendingGrading === true;

  return (
    <div className="min-h-screen bg-phila-gray-50">
      <Head title={`Résultat — ${assessment.title}`} />

      <div className="container-phila max-w-2xl py-10">
        <div
          className={`card text-center ${
            pending
              ? 'border-blue-200 bg-blue-50'
              : passed
                ? 'border-green-200 bg-green-50'
                : 'border-amber-200 bg-amber-50'
          }`}
        >
          <p className="text-xs font-semibold uppercase tracking-[0.2em] text-phila-orange">
            {isModuleExitQuiz ? 'Quiz fin de module ECAP' : 'Résultat du test'}
          </p>
          <h1 className="mt-2 font-display text-2xl font-bold text-phila-black">{assessment.title}</h1>
          {moduleName && (
            <p className="mt-1 text-sm text-phila-gray-600">{moduleName}</p>
          )}

          {pending ? (
            <>
              <p className="mt-6 font-display text-xl font-bold text-blue-900">En attente de correction</p>
              <p className="mt-2 text-sm text-blue-800">
                Vos réponses rédigées seront corrigées par un enseignant ou un superviseur. Vous serez notifié dès que
                la correction sera disponible.
              </p>
              {score !== null && (
                <p className="mt-4 text-sm text-phila-gray-600">
                  Score provisoire (QCM uniquement) : {score}% — seuil requis : {passingScore}%
                </p>
              )}
            </>
          ) : (
            <>
              <p className="mt-6 font-display text-4xl font-extrabold text-phila-orange">
                {score}%
              </p>
              <p className="mt-2 text-sm text-phila-gray-600">
                Seuil requis : {passingScore}%
              </p>

              <p className={`mt-4 text-sm font-semibold ${passed ? 'text-green-800' : 'text-amber-900'}`}>
                {passed
                  ? (isModuleExitQuiz
                    ? 'Félicitations ! Vous pouvez accéder au module suivant.'
                    : 'Test réussi !')
                  : 'Score insuffisant. Consultez la correction ci-dessous et révisez les chapitres indiqués.'}
              </p>
            </>
          )}
        </div>

        {mcqQuestions.length > 0 && (
          <div className="mt-6 card">
            <h2 className="font-display text-lg font-bold text-phila-black">Correction du quiz</h2>
            <p className="mt-1 text-sm text-phila-gray-600">
              Vos réponses comparées aux bonnes réponses. Reprenez le cours sur les questions ratées.
            </p>
            <ul className="mt-4 space-y-4">
              {mcqQuestions.map((item, index) => (
                <li
                  key={`${item.stem}-${index}`}
                  className={`rounded-xl border p-4 ${item.is_correct ? 'border-green-200 bg-green-50/50' : 'border-amber-200 bg-amber-50/40'}`}
                >
                  <p className="text-sm font-semibold text-phila-black">
                    Question {index + 1}
                    <span className={`ml-2 text-xs uppercase tracking-wide ${item.is_correct ? 'text-green-700' : 'text-amber-800'}`}>
                      {item.is_correct ? 'Correcte' : 'Incorrecte'}
                    </span>
                  </p>
                  <p className="mt-2 text-sm text-phila-gray-700">{item.stem}</p>

                  <dl className="mt-3 space-y-1 text-sm">
                    <div className="flex flex-wrap gap-x-2">
                      <dt className="font-medium text-phila-gray-600">Votre réponse :</dt>
                      <dd className={item.is_correct ? 'text-green-800' : 'text-amber-900'}>
                        {item.selected_label ?? '—'}
                      </dd>
                    </div>
                    {!item.is_correct && (
                      <div className="flex flex-wrap gap-x-2">
                        <dt className="font-medium text-phila-gray-600">Bonne réponse :</dt>
                        <dd className="font-semibold text-green-800">{item.correct_label ?? '—'}</dd>
                      </div>
                    )}
                  </dl>

                  {!item.is_correct && item.chapter_id && (
                    <Link
                      href={`/mon-espace/cours/${item.chapter_id}`}
                      className="mt-3 inline-flex text-sm font-semibold text-phila-orange hover:underline"
                    >
                      Revoir le cours : {item.chapter_title}
                    </Link>
                  )}
                </li>
              ))}
            </ul>
          </div>
        )}

        {writtenQuestions.length > 0 && (
          <div className="mt-6 card">
            <h2 className="font-display text-lg font-bold text-phila-black">Réponses rédigées</h2>
            <ul className="mt-4 space-y-4">
              {writtenQuestions.map((item, index) => (
                <li key={`${item.stem}-${index}`} className="rounded-xl border border-phila-gray-100 p-4">
                  <p className="text-sm font-semibold text-phila-black">{item.stem}</p>
                  <p className="mt-2 text-sm text-phila-gray-700">{item.answer_text || '—'}</p>
                  {(item.answered_at || submittedAt) && (
                    <p className="mt-1 text-[10px] text-phila-gray-400">
                      Répondu le {item.answered_at ?? submittedAt}
                    </p>
                  )}

                  {pending ? (
                    <p className="mt-2 text-xs font-semibold uppercase tracking-wide text-blue-700">
                      En attente de correction
                    </p>
                  ) : (
                    <>
                      <p className="mt-2 text-sm text-phila-gray-600">
                        Note : {item.points_awarded ?? 0} / {item.max_points}
                      </p>
                      {item.grader_feedback && (
                        <div className="mt-2 rounded-xl bg-phila-orange-pale/50 px-3 py-2 text-sm">
                          <p className="text-[10px] font-semibold uppercase text-phila-orange">
                            Correction{gradedByName ? ` · ${gradedByName}` : ''}
                            {gradedAt && (
                              <span className="ml-2 font-normal normal-case text-phila-gray-500">· {gradedAt}</span>
                            )}
                          </p>
                          <p className="mt-1 text-phila-gray-800">{item.grader_feedback}</p>
                        </div>
                      )}
                    </>
                  )}
                </li>
              ))}
            </ul>

            {!pending && (
              <div className="mt-6 border-t border-phila-gray-100 pt-4">
                <h3 className="text-sm font-semibold text-phila-black">Avis des acteurs ECAP</h3>
                <p className="mt-1 text-xs text-phila-gray-500">
                  Compléments ou remarques des enseignants et superviseurs après la correction.
                </p>
                {staffComments.length > 0 ? (
                  <ul className="mt-3 space-y-3">
                    {staffComments.map((comment) => (
                      <li
                        key={comment.id ?? `${comment.author_name}-${comment.created_at}`}
                        className="rounded-xl bg-phila-gray-50 px-3 py-2 text-sm"
                      >
                        <p className="text-xs font-semibold text-phila-black">
                          {comment.author_name}
                          {comment.created_at && (
                            <span className="ml-2 font-normal text-phila-gray-400">· {comment.created_at}</span>
                          )}
                        </p>
                        <p className="mt-1 whitespace-pre-wrap text-phila-gray-800">{comment.body}</p>
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p className="mt-3 text-xs text-phila-gray-400">Aucun avis complémentaire pour l&apos;instant.</p>
                )}
              </div>
            )}
          </div>
        )}

        {!passed && !pending && reviews?.length > 0 && mcqQuestions.length === 0 && (
          <div className="mt-6 card">
            <h2 className="font-display text-lg font-bold text-phila-black">Chapitres à réviser</h2>
            <ul className="mt-4 space-y-3">
              {reviews.map((review) => (
                <li key={`${review.chapter_id}-${review.question_stem}`} className="rounded-xl border border-phila-gray-100 p-4">
                  <p className="text-sm text-phila-gray-600">{review.question_stem}</p>
                  {review.correct_label && (
                    <p className="mt-1 text-sm text-green-800">
                      Bonne réponse : {review.correct_label}
                    </p>
                  )}
                  <Link
                    href={`/mon-espace/cours/${review.chapter_id}`}
                    className="mt-2 inline-flex text-sm font-semibold text-phila-orange hover:underline"
                  >
                    Revoir : {review.chapter_title}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        )}

        <div className="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
          {!passed && !pending && (
            <Link href={`/mon-espace/tests/${assessment.id}`} className="btn btn-accent px-6 text-center">
              Réessayer le quiz
            </Link>
          )}
          <Link href={historyUrl} className="btn btn-accent px-6 text-center">
            Retour à mes quiz
          </Link>
          <Link href="/mon-espace" className="btn btn-outline px-6 text-center">
            Retour à mon espace
          </Link>
        </div>
      </div>
    </div>
  );
}
