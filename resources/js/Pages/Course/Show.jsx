import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import LoadingButton from '../../Components/UI/LoadingButton';
import UserAvatar from '../../Components/UI/UserAvatar';
import CourseVideoPlayer from '../../Components/Course/CourseVideoPlayer';

/**
 * Badge de statut pour tests et TP.
 */
function StatusBadge({ status, passed }) {
  const map = {
    passed: 'bg-green-100 text-green-800',
    not_submitted: 'bg-phila-gray-100 text-phila-gray-600',
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    failed: 'bg-red-100 text-red-800',
  };

  const labels = {
    passed: 'Réussi',
    not_submitted: 'Non remis',
    pending: 'En attente',
    approved: 'Validé',
    rejected: 'Refusé',
    failed: 'À repasser',
  };

  const key = passed ? 'passed' : status;

  return (
    <span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${map[key] ?? map.not_submitted}`}>
      {labels[key] ?? status}
    </span>
  );
}

/**
 * Carte profil mentor Métamorpho.
 */
function MentorCard({ mentor }) {
  if (!mentor) {
    return (
      <div className="rounded-xl border border-dashed border-phila-gray-200 p-6 text-center text-sm text-phila-gray-600">
        Votre mentor Métamorpho sera bientôt assigné par l&apos;équipe PHILA.
      </div>
    );
  }

  return (
    <div className="card space-y-4">
      <div className="flex items-start gap-4">
        <UserAvatar
          avatarUrl={mentor.avatar_url}
          name={mentor.name}
          initials={mentor.initials}
          size="md"
        />
        <div>
          <h3 className="font-display text-lg font-bold">{mentor.name}</h3>
          {mentor.gender && (
            <p className="text-sm text-phila-gray-600">{mentor.gender}</p>
          )}
          <p className="text-sm text-phila-gray-600">Votre mentor Métamorpho</p>
          {mentor.started_at && (
            <p className="mt-1 text-xs text-phila-gray-500">Accompagnement depuis le {mentor.started_at}</p>
          )}
        </div>
      </div>
      <Link href="/mon-espace/mentor" className="btn btn-accent text-sm">
        Voir le profil &amp; messages
      </Link>
    </div>
  );
}

/**
 * Formulaire de remise de TP inline.
 */
function TpSubmitForm({ tp, isReviewMode }) {
  const form = useForm({ answer_text: '', file: null });

  const submit = (event) => {
    event.preventDefault();
    form.post(`/mon-espace/tp/${tp.id}/soumettre`, {
      forceFormData: true,
      preserveScroll: true,
    });
  };

  if (!tp.can_submit) {
    return null;
  }

  if (isReviewMode && tp.status === 'approved') {
    return null;
  }

  return (
    <form onSubmit={submit} className="mt-4 space-y-3 rounded-xl border border-phila-gray-100 bg-phila-gray-50 p-4">
      <textarea
        className="input-field min-h-[100px]"
        placeholder="Votre réponse ou compte-rendu…"
        value={form.data.answer_text}
        onChange={(e) => form.setData('answer_text', e.target.value)}
      />
      <input
        type="file"
        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
        onChange={(e) => form.setData('file', e.target.files[0])}
        className="text-sm"
      />
      <LoadingButton
        type="submit"
        processing={form.processing}
        loadingText="Envoi…"
        className="btn btn-accent text-sm"
      >
        Remettre le TP
      </LoadingButton>
    </form>
  );
}

/**
 * Lecteur de cours avec onglets tests, TP et mentor.
 */
export default function Show({
  chapter,
  cursus,
  contentBlocks,
  curriculum,
  nextChapter = null,
  requirements,
  mentor,
  instructor,
  readOnlyOnline = false,
}) {
  const { flash } = usePage().props;
  const completeForm = useForm({});

  const hasQuiz = requirements?.has_quiz ?? (requirements?.quizzes?.length > 0);
  const hasTp = requirements?.has_tp ?? (requirements?.tps?.length > 0);
  const isReviewMode = requirements?.isReviewMode ?? chapter.is_completed;
  const canComplete = requirements?.canComplete ?? true;
  const blocking = requirements?.blocking ?? [];

  const defaultTab = 'content';
  const [activeTab, setActiveTab] = useState(defaultTab);

  const videoBlock = contentBlocks.find((block) => block.type === 'video' && (block.url || block.media_url));
  const textBlocks = contentBlocks.filter((block) => block.type === 'text' || (block.type !== 'video' && block.body));

  const handleComplete = () => {
    completeForm.post(`/mon-espace/cours/${chapter.id}/terminer`, {
      preserveScroll: true,
    });
  };

  const showNextChapterButton = isReviewMode && nextChapter?.id && !readOnlyOnline;
  const showCompleteButton = !isReviewMode && !readOnlyOnline;
  const homeHref = `/mon-espace${cursus?.slug ? `?cursus=${cursus.slug}` : ''}`;
  const showReturnHomeButton = isReviewMode && !nextChapter?.id && !readOnlyOnline;

  const tabs = [
    { id: 'content', label: 'Contenu', highlight: false },
    { id: 'tests', label: 'Tests', count: requirements?.quizzes?.length ?? 0, highlight: hasQuiz },
    { id: 'tp', label: 'TP', count: requirements?.tps?.length ?? 0, highlight: hasTp },
  ];

  if (cursus?.slug === 'metamorpho') {
    tabs.push({ id: 'mentor', label: 'Mon mentor', highlight: !!mentor });
  }

  return (
    <div className="course-player min-h-screen bg-phila-gray-50">
      <Head title={chapter.title} />

      <header className="course-player-topbar sticky top-0 z-50">
        <div className="mx-auto flex max-w-[1600px] items-center justify-between gap-4 px-4 py-3">
          <div className="flex min-w-0 flex-1 items-center gap-3">
            <Link href={`/mon-espace${cursus ? `?cursus=${cursus.slug}` : ''}`} className="course-player-back shrink-0">
              ← Mon espace
            </Link>
            <div className="min-w-0">
              {cursus && (
                <p className="truncate text-[10px] font-semibold uppercase tracking-wider text-phila-orange">
                  {cursus.name}
                </p>
              )}
              <h1 className="truncate font-display text-sm font-bold text-white sm:text-base">{chapter.title}</h1>
            </div>
          </div>
          {!readOnlyOnline && (
            <>
              {showNextChapterButton && (
                <Link
                  href={`/mon-espace/cours/${nextChapter.id}`}
                  className="btn btn-accent shrink-0 px-4 py-2 text-xs sm:text-sm"
                >
                  Chapitre suivant →
                </Link>
              )}
              {showReturnHomeButton && (
                <Link
                  href={homeHref}
                  className="btn btn-accent shrink-0 px-4 py-2 text-xs sm:text-sm"
                >
                  Retour à l&apos;accueil →
                </Link>
              )}
              {showCompleteButton && (
                <LoadingButton
                  type="button"
                  onClick={handleComplete}
                  processing={completeForm.processing}
                  loadingText="Enregistrement…"
                  disabled={!canComplete}
                  title={!canComplete ? blocking.join(' ') : ''}
                  className="btn btn-accent shrink-0 px-4 py-2 text-xs sm:text-sm disabled:opacity-50"
                >
                  Terminer l&apos;étape →
                </LoadingButton>
              )}
            </>
          )}
          {readOnlyOnline && (
            <span className="shrink-0 rounded-lg bg-amber-500/20 px-3 py-1.5 text-xs font-semibold text-amber-100">
              Lecture seule — présentiel
            </span>
          )}
          {isReviewMode && !showNextChapterButton && !showReturnHomeButton && (
            <span className="shrink-0 rounded-lg bg-green-500/20 px-3 py-1.5 text-xs font-semibold text-green-100">
              Étape validée — reprise libre
            </span>
          )}
        </div>
      </header>

      {(flash?.status || flash?.error) && (
        <div className={`border-b px-4 py-2 text-center text-sm ${flash.error ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800'}`}>
          {flash.status || flash.error}
        </div>
      )}

      {readOnlyOnline && (
        <div className="border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm text-amber-900">
          Mode présentiel : vous consultez le contenu sans progression en ligne (quiz, TP et validation d&apos;étape désactivés).
        </div>
      )}

      {!isReviewMode && !canComplete && blocking.length > 0 && (
        <div className="border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          <strong>Pour débloquer l&apos;étape suivante :</strong>
          <ul className="mt-1 list-inside list-disc">
            {blocking.map((reason) => (
              <li key={reason}>{reason}</li>
            ))}
          </ul>
        </div>
      )}

      {instructor && (
        <div className="border-b border-phila-gray-100 bg-white px-4 py-3">
          <div className="mx-auto flex max-w-[1600px] items-center gap-3">
            {instructor.avatar_url ? (
              <img src={instructor.avatar_url} alt="" className="h-10 w-10 rounded-full object-cover ring-2 ring-phila-orange/20" />
            ) : (
              <span className="flex h-10 w-10 items-center justify-center rounded-full bg-phila-black text-sm font-bold text-white">{instructor.initials}</span>
            )}
            <div>
              <p className="text-[10px] font-semibold uppercase tracking-wider text-phila-orange">Enseignant de cette étape</p>
              <p className="font-display font-bold text-phila-black">{instructor.name}</p>
            </div>
            {(hasQuiz || hasTp) && (
              <div className="ml-auto flex flex-wrap gap-2">
                {hasQuiz && <span className="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-semibold text-blue-800">📝 Test</span>}
                {hasTp && <span className="rounded-full bg-purple-100 px-2.5 py-1 text-[10px] font-semibold text-purple-800">📋 TP</span>}
              </div>
            )}
          </div>
        </div>
      )}

      {(hasQuiz || hasTp) && (
        <div className="border-b border-blue-100 bg-blue-50 px-4 py-3">
          <div className="mx-auto max-w-[1600px]">
            <p className="text-sm font-semibold text-blue-900">Exigences de cette étape (imposées par le formateur)</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {hasQuiz && (
                <button type="button" onClick={() => setActiveTab('tests')} className="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 hover:bg-blue-200">
                  📝 {requirements?.quizzes?.length ?? 0} test(s)
                </button>
              )}
              {hasTp && (
                <button type="button" onClick={() => setActiveTab('tp')} className="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-800 hover:bg-purple-200">
                  📋 {requirements?.tps?.length ?? 0} TP
                </button>
              )}
            </div>
          </div>
        </div>
      )}

      {isReviewMode && (
        <div className="border-b border-green-200 bg-green-50 px-4 py-2 text-center text-sm text-green-800">
          Vous revisitez une étape déjà terminée. Les tests et TP ne bloquent plus votre progression.
        </div>
      )}

      <div className="mx-auto max-w-[1600px] border-b border-phila-gray-100 bg-white px-4">
        <div className="flex gap-1 overflow-x-auto">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              type="button"
              onClick={() => setActiveTab(tab.id)}
              className={`shrink-0 border-b-2 px-4 py-3 text-sm font-medium transition ${
                activeTab === tab.id
                  ? 'border-phila-orange text-phila-orange'
                  : 'border-transparent text-phila-gray-500 hover:text-phila-black'
              } ${tab.highlight ? 'font-semibold' : ''}`}
            >
              {tab.label}
              {tab.count > 0 && ` (${tab.count})`}
              {tab.highlight && activeTab !== tab.id && (
                <span className="ml-1 inline-block h-2 w-2 rounded-full bg-phila-orange" />
              )}
            </button>
          ))}
        </div>
      </div>

      <div className="mx-auto grid max-w-[1600px] gap-0 lg:grid-cols-[1fr_360px]">
        <main className="min-h-[calc(100vh-120px)] bg-white">
          {activeTab === 'content' && (
            <>
              <CourseVideoPlayer
                youtubeUrl={videoBlock?.url}
                streamUrl={videoBlock?.stream_url}
                posterUrl={videoBlock?.poster_url}
                title={videoBlock?.title || chapter.title}
                chapterTitle={chapter.title}
              />
              {videoBlock?.body && (
                <p className="border-b border-phila-gray-100 bg-phila-gray-50 px-4 py-3 text-center text-xs text-phila-gray-600 sm:px-6">
                  {videoBlock.body}
                </p>
              )}
              <div className="space-y-6 px-4 py-6 sm:px-6">
                {textBlocks.length === 0 && !videoBlock ? (
                  <div className="rounded-xl bg-phila-orange-pale px-4 py-4 text-sm text-phila-gray-600">
                    Le contenu détaillé sera bientôt disponible.
                  </div>
                ) : (
                  textBlocks.map((block) => (
                    <article key={block.id} className="rounded-xl border border-phila-gray-100 p-5">
                      {block.title && <h3 className="font-display font-bold">{block.title}</h3>}
                      {block.body && (
                        <div
                          className="prose prose-sm mt-3 max-w-none whitespace-pre-line text-phila-gray-600"
                          dangerouslySetInnerHTML={{ __html: block.body.replace(/\n/g, '<br>') }}
                        />
                      )}
                    </article>
                  ))
                )}

                {showNextChapterButton && (
                  <div className="rounded-xl border border-green-200 bg-green-50 px-4 py-4 text-center">
                    <p className="text-sm text-green-900">Étape terminée — poursuivez avec le chapitre suivant.</p>
                    <Link
                      href={`/mon-espace/cours/${nextChapter.id}`}
                      className="btn btn-accent mt-3 inline-flex px-6 py-2 text-sm"
                    >
                      {nextChapter.title} →
                    </Link>
                  </div>
                )}

                {showReturnHomeButton && (
                  <div className="rounded-xl border border-green-200 bg-green-50 px-4 py-4 text-center">
                    <p className="text-sm text-green-900">
                      Félicitations ! Vous avez terminé la dernière étape de ce parcours.
                    </p>
                    <Link
                      href={homeHref}
                      className="btn btn-accent mt-3 inline-flex px-6 py-2 text-sm"
                    >
                      Retour à l&apos;accueil →
                    </Link>
                  </div>
                )}
              </div>
            </>
          )}

          {activeTab === 'tests' && (
            <div className="space-y-4 p-6">
              <h2 className="font-display text-lg font-bold">Tests de l&apos;étape</h2>
              {readOnlyOnline && (
                <p className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                  Les tests en ligne ne sont pas disponibles en mode présentiel.
                </p>
              )}
              {(requirements?.quizzes ?? []).length === 0 ? (
                <p className="text-sm text-phila-gray-600">Aucun test pour cette étape.</p>
              ) : (
                requirements.quizzes.map((quiz) => (
                  <div key={quiz.id} className="card flex flex-wrap items-center justify-between gap-3">
                    <div>
                      <p className="font-semibold">{quiz.title}</p>
                      <p className="text-xs text-phila-gray-600">
                        Seuil : {quiz.passing_score}% · Tentatives restantes : {quiz.remaining_attempts}
                        {quiz.last_score !== null && ` · Dernier score : ${quiz.last_score}%`}
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <StatusBadge status={quiz.last_status} passed={quiz.passed} />
                      {quiz.can_start && !readOnlyOnline && (
                        <Link href={`/mon-espace/tests/${quiz.id}`} className="btn btn-accent text-sm">
                          Passer le test
                        </Link>
                      )}
                      {quiz.passed && !readOnlyOnline && (
                        <Link href={`/mon-espace/tests/${quiz.id}`} className="btn btn-outline text-sm">
                          Voir
                        </Link>
                      )}
                    </div>
                  </div>
                ))
              )}
            </div>
          )}

          {activeTab === 'tp' && (
            <div className="space-y-4 p-6">
              <h2 className="font-display text-lg font-bold">Travaux pratiques</h2>
              {readOnlyOnline && (
                <p className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                  Les TP en ligne ne sont pas disponibles en mode présentiel.
                </p>
              )}
              {(requirements?.tps ?? []).length === 0 ? (
                <p className="text-sm text-phila-gray-600">Aucun TP pour cette étape.</p>
              ) : (
                requirements.tps.map((tp) => (
                  <div key={tp.id} className="card">
                    <div className="flex flex-wrap items-start justify-between gap-3">
                      <div>
                        <p className="font-semibold">{tp.title}</p>
                        {tp.submitted_at && (
                          <p className="text-xs text-phila-gray-600">Remis le {tp.submitted_at}</p>
                        )}
                        {tp.mentor_status === 'pending' && (
                          <p className="mt-2 text-xs text-amber-700">En attente de l&apos;aval de votre mentor.</p>
                        )}
                        {tp.mentor_notes && (
                          <p className="mt-2 rounded-lg bg-phila-gray-50 p-3 text-sm"><strong>Mentor :</strong> {tp.mentor_notes}</p>
                        )}
                        {tp.grader_notes && (
                          <p className="mt-2 rounded-lg bg-phila-gray-50 p-3 text-sm text-phila-gray-600">
                            <strong>Retour formateur :</strong> {tp.grader_notes}
                          </p>
                        )}
                      </div>
                      <StatusBadge status={tp.status} />
                    </div>
                    {!readOnlyOnline && (
                      <TpSubmitForm tp={tp} chapterId={chapter.id} isReviewMode={isReviewMode} />
                    )}
                  </div>
                ))
              )}
            </div>
          )}

          {activeTab === 'mentor' && (
            <div className="p-6">
              <h2 className="mb-4 font-display text-lg font-bold">Votre mentor Métamorpho</h2>
              <MentorCard mentor={mentor} />
            </div>
          )}
        </main>

        <aside className="course-player-sidebar border-l border-phila-gray-100 bg-white lg:sticky lg:top-[52px] lg:h-[calc(100vh-52px)] lg:overflow-y-auto">
          <div className="border-b border-phila-gray-100 p-4">
            <h2 className="font-display text-sm font-bold">Parcours</h2>
          </div>
          <ul className="divide-y divide-phila-gray-100">
            {curriculum.map((item) => {
              const isCurrent = item.id === chapter.id;
              const isLocked = item.status === 'locked';
              const isDone = item.status === 'completed';
              return (
                <li key={item.id}>
                  {isLocked ? (
                    <div className="flex items-center gap-3 px-4 py-3 opacity-50">
                      <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-phila-gray-100 text-xs">🔒</span>
                      <span className="text-sm text-phila-gray-400">{item.title}</span>
                    </div>
                  ) : (
                    <Link
                      href={`/mon-espace/cours/${item.id}`}
                      className={`flex items-center gap-3 px-4 py-3 transition hover:bg-phila-orange-pale/50 ${isCurrent ? 'border-l-2 border-phila-orange bg-phila-orange-pale' : ''}`}
                    >
                      <span className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold ${isDone ? 'bg-green-100 text-green-700' : isCurrent ? 'bg-phila-orange text-white' : 'bg-phila-gray-100'}`}>
                        {isDone ? '✓' : item.order}
                      </span>
                      <span className={`min-w-0 flex-1 text-sm ${isCurrent ? 'font-semibold' : 'text-phila-gray-600'}`}>{item.title}</span>
                      {(item.has_quiz || item.has_tp) && (
                        <span className="flex shrink-0 gap-0.5">
                          {item.has_quiz && <span className="text-[10px]" title="Test">📝</span>}
                          {item.has_tp && <span className="text-[10px]" title="TP">📋</span>}
                        </span>
                      )}
                    </Link>
                  )}
                </li>
              );
            })}
          </ul>
        </aside>
      </div>
    </div>
  );
}
