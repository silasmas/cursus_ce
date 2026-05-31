import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';

/**
 * Correction d'un quiz avec réponses rédigées (verrou collaboratif).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function StaffQuizGradingShow({ attempt: initialAttempt, lock_acquired: lockAcquired = false }) {
  const { flash } = usePage().props;
  const [attempt, setAttempt] = useState(initialAttempt);
  const canEdit = attempt?.can_edit === true;
  const isGraded = attempt?.is_graded === true;
  const isLockedByOther = attempt?.lock?.is_locked === true;
  const lockedByName = attempt?.lock?.locked_by?.name ?? 'un autre correcteur';

  const [grades, setGrades] = useState(() => buildGrades(initialAttempt));
  const [submitting, setSubmitting] = useState(false);
  const [commentBody, setCommentBody] = useState('');
  const [commentSubmitting, setCommentSubmitting] = useState(false);

  useEffect(() => {
    setAttempt(initialAttempt);
    setGrades(buildGrades(initialAttempt));
  }, [initialAttempt]);

  useEffect(() => {
    const releaseUrl = `/ecap/acteurs/corrections-quiz/${attempt.id}/unlock`;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const release = () => {
      if (!lockAcquired) {
        return;
      }

      fetch(releaseUrl, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        keepalive: true,
        body: JSON.stringify({}),
      }).catch(() => {});
    };

    const handleBeforeUnload = () => release();

    window.addEventListener('beforeunload', handleBeforeUnload);

    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
      release();
    };
  }, [attempt.id, lockAcquired]);

  const updateGrade = (index, field, value) => {
    setGrades((previous) =>
      previous.map((item, itemIndex) => (itemIndex === index ? { ...item, [field]: value } : item)),
    );
  };

  const submitGrades = async () => {
    setSubmitting(true);

    try {
      const url = `/ecap/acteurs/corrections-quiz/${attempt.id}`;
      const method = isGraded ? 'PATCH' : 'POST';
      const payload = {
        grades: grades.map((item) => ({
          answer_id: item.answer_id,
          points_awarded: Number(item.points_awarded),
          grader_feedback: item.grader_feedback || null,
        })),
      };

      const response = await fetch(url, {
        method,
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        window.alert(data.message ?? 'Impossible d\'enregistrer la correction.');
        return;
      }

      const data = await response.json();
      if (data.attempt) {
        setAttempt(data.attempt);
        setGrades(buildGrades(data.attempt));
      }

      router.reload({ only: ['attempt', 'lock_acquired'] });
    } finally {
      setSubmitting(false);
    }
  };

  const submitComment = async () => {
    if (!commentBody.trim()) {
      return;
    }

    setCommentSubmitting(true);

    try {
      const response = await fetch(`/ecap/acteurs/corrections-quiz/${attempt.id}/avis`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ body: commentBody.trim() }),
      });

      if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        window.alert(data.message ?? 'Impossible de publier l\'avis.');
        return;
      }

      const data = await response.json();
      if (data.attempt) {
        setAttempt(data.attempt);
      }

      setCommentBody('');
      router.reload({ only: ['attempt', 'lock_acquired'] });
    } finally {
      setCommentSubmitting(false);
    }
  };

  return (
    <EcapStaffLayout active="quiz-grading">
      <Head title={`Correction — ${attempt.assessment_title}`} />

      <div className="mx-auto max-w-3xl px-4 py-6">
        <Link href="/ecap/acteurs/corrections-quiz" className="text-xs font-semibold text-phila-orange hover:underline">
          ← Retour à la liste
        </Link>

        <h1 className="mt-3 font-display text-2xl font-bold text-phila-black">Correction quiz</h1>
        <p className="mt-1 text-sm text-phila-gray-600">
          {attempt.student_name} · {attempt.assessment_title}
          {attempt.module_name && ` · ${attempt.module_name}`}
        </p>
        <p className="text-xs text-phila-gray-400">Soumis le {attempt.submitted_at}</p>

        {isGraded && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
            <strong>Correction enregistrée</strong>
            {attempt.graded_by_name && ` par ${attempt.graded_by_name}`}
            {attempt.graded_at && ` le ${attempt.graded_at}`}
            {attempt.score !== null && (
              <span className="ml-2 font-semibold">
                · Score {attempt.score}%{attempt.passed ? ' · Réussi' : ''}
              </span>
            )}
          </div>
        )}

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}

        {flash?.error && (
          <div className="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {flash.error}
          </div>
        )}

        {isLockedByOther && (
          <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <strong>Correction en cours</strong> — {lockedByName} corrige actuellement cette tentative.
            Consultation en lecture seule.
          </div>
        )}

        <ul className="mt-6 space-y-4">
          {(attempt.written_answers ?? []).map((answer, index) => (
            <li key={answer.id} className="rounded-2xl border border-phila-gray-100 bg-white p-4 shadow-sm">
              <p className="text-sm font-semibold text-phila-black">{answer.stem}</p>
              <p className="mt-2 rounded-xl bg-phila-gray-50 px-3 py-2 text-sm text-phila-gray-800">
                {answer.answer_text || '— Aucune réponse —'}
              </p>
              <p className="mt-2 text-[10px] font-semibold uppercase text-phila-gray-500">
                Points max : {answer.max_points}
              </p>

              {canEdit && (
                <div className="mt-4 flex flex-wrap items-end gap-3 border-t border-phila-gray-100 pt-3">
                  <div>
                    <label className="text-[10px] font-semibold uppercase text-phila-gray-500">Points</label>
                    <input
                      type="number"
                      min="0"
                      max={answer.max_points}
                      step="0.5"
                      className="mt-1 w-24 rounded-lg border border-phila-gray-200 px-2 py-1.5 text-sm"
                      value={grades[index]?.points_awarded ?? ''}
                      onChange={(event) => updateGrade(index, 'points_awarded', event.target.value)}
                    />
                  </div>
                  <div className="min-w-[200px] flex-1">
                    <label className="text-[10px] font-semibold uppercase text-phila-gray-500">Feedback</label>
                    <input
                      type="text"
                      className="mt-1 w-full rounded-lg border border-phila-gray-200 px-2 py-1.5 text-sm"
                      value={grades[index]?.grader_feedback ?? ''}
                      onChange={(event) => updateGrade(index, 'grader_feedback', event.target.value)}
                    />
                  </div>
                </div>
              )}

              {!canEdit && answer.points_awarded !== null && (
                <div className="mt-3 rounded-xl bg-phila-orange-pale/40 px-3 py-2 text-sm text-phila-gray-800">
                  <p className="font-semibold text-phila-black">
                    Note : {answer.points_awarded} / {answer.max_points}
                  </p>
                  {answer.grader_feedback && (
                    <p className="mt-1 text-phila-gray-700">Feedback : {answer.grader_feedback}</p>
                  )}
                </div>
              )}
            </li>
          ))}
        </ul>

        {canEdit && (
          <div className="mt-6">
            <button
              type="button"
              disabled={submitting || grades.some((item) => item.points_awarded === '')}
              onClick={submitGrades}
              className="btn btn-accent px-6 py-2 text-sm"
            >
              {submitting
                ? 'Enregistrement…'
                : isGraded
                  ? 'Enregistrer les modifications'
                  : 'Enregistrer la correction'}
            </button>
          </div>
        )}

        {attempt.can_comment && (
          <section className="mt-8 rounded-2xl border border-phila-gray-100 bg-white p-4 shadow-sm">
            <h2 className="font-display text-sm font-bold text-phila-black">Avis des acteurs ECAP</h2>
            <p className="mt-1 text-xs text-phila-gray-500">
              Compléments ou remarques après la correction (visible par les autres acteurs).
            </p>

            {(attempt.comments ?? []).length > 0 ? (
              <ul className="mt-4 space-y-3">
                {attempt.comments.map((comment) => (
                  <li key={comment.id} className="rounded-xl bg-phila-gray-50 px-3 py-2 text-sm">
                    <p className="text-xs font-semibold text-phila-black">
                      {comment.author_name}
                      <span className="ml-2 font-normal text-phila-gray-400">{comment.created_at}</span>
                    </p>
                    <p className="mt-1 whitespace-pre-wrap text-phila-gray-800">{comment.body}</p>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="mt-4 text-xs text-phila-gray-400">Aucun avis pour l&apos;instant.</p>
            )}

            <div className="mt-4 border-t border-phila-gray-100 pt-4">
              <label className="text-[10px] font-semibold uppercase text-phila-gray-500">Ajouter un avis</label>
              <textarea
                className="mt-1 w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
                rows={3}
                value={commentBody}
                onChange={(event) => setCommentBody(event.target.value)}
                placeholder="Partager un complément ou une remarque…"
              />
              <button
                type="button"
                disabled={commentSubmitting || !commentBody.trim()}
                onClick={submitComment}
                className="btn btn-outline mt-2 px-4 py-1.5 text-xs"
              >
                {commentSubmitting ? 'Publication…' : 'Publier l\'avis'}
              </button>
            </div>
          </section>
        )}
      </div>
    </EcapStaffLayout>
  );
}

/**
 * Construit l'état local des notes à partir du payload serveur.
 *
 * @param {Object|null} attemptPayload
 * @returns {Array}
 */
function buildGrades(attemptPayload) {
  return (attemptPayload?.written_answers ?? []).map((answer) => ({
    answer_id: answer.id,
    points_awarded: answer.points_awarded ?? '',
    grader_feedback: answer.grader_feedback ?? '',
  }));
}
