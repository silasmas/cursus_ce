import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import LoadingButton from '../../Components/UI/LoadingButton';

/**
 * Formate des secondes en mm:ss.
 *
 * @param {number} totalSeconds Secondes restantes
 * @returns {string}
 */
function formatDuration(totalSeconds) {
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = totalSeconds % 60;

  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

/**
 * Interface de passage d'un test (QCM + réponses rédigées).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Take({
  attempt_id,
  assessment,
  questions,
  chapterId,
  expires_at,
  remaining_seconds,
  server_now,
}) {
  const { flash } = usePage().props;
  const [answers, setAnswers] = useState({});
  const [secondsLeft, setSecondsLeft] = useState(remaining_seconds ?? null);
  const answersRef = useRef(answers);
  const autoSubmittedRef = useRef(false);

  const form = useForm({ answers: {} });

  const setMcq = (questionId, optionId) => {
    setAnswers((prev) => {
      const next = { ...prev, [questionId]: { ...prev[questionId], option_id: optionId } };
      answersRef.current = next;

      return next;
    });
  };

  const setWritten = (questionId, text) => {
    setAnswers((prev) => {
      const next = { ...prev, [questionId]: { ...prev[questionId], text } };
      answersRef.current = next;

      return next;
    });
  };

  const submitAnswers = (event) => {
    if (event) {
      event.preventDefault();
    }

    if (autoSubmittedRef.current && form.processing) {
      return;
    }

    autoSubmittedRef.current = true;
    form.setData('answers', answersRef.current);
    form.post(`/mon-espace/tests/${assessment.id}/tenter/${attempt_id}/soumettre`);
  };

  useEffect(() => {
    answersRef.current = answers;
  }, [answers]);

  useEffect(() => {
    if (!expires_at || !server_now) {
      return undefined;
    }

    const serverOffsetMs = new Date(server_now).getTime() - Date.now();

    const tick = () => {
      const nowMs = Date.now() + serverOffsetMs;
      const remaining = Math.max(0, Math.floor((new Date(expires_at).getTime() - nowMs) / 1000));
      setSecondsLeft(remaining);

      if (remaining <= 0 && !autoSubmittedRef.current) {
        submitAnswers();
      }
    };

    tick();
    const intervalId = window.setInterval(tick, 1000);

    return () => window.clearInterval(intervalId);
  }, [assessment.id, attempt_id, expires_at, server_now]);

  const isUrgent = secondsLeft !== null && secondsLeft <= 60;

  return (
    <div className="min-h-screen bg-phila-gray-50">
      <Head title={assessment.title} />
      <div className="container-phila max-w-2xl py-10">
        {flash?.error && (
          <div className="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{flash.error}</div>
        )}
        <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 className="font-display text-2xl font-bold">{assessment.title}</h1>
            <p className="text-sm text-phila-gray-600">
              Seuil : {assessment.passing_score}%
              {assessment.time_limit_seconds > 0
                ? ` · Durée : ${Math.ceil(assessment.time_limit_seconds / 60)} min`
                : ' · Durée illimitée'}
              {' · '}Répondez à toutes les questions
            </p>
          </div>
          {secondsLeft !== null && (
            <div className={`rounded-xl px-4 py-2 text-center ${isUrgent ? 'bg-red-50 text-red-800' : 'bg-phila-orange-pale text-phila-black'}`}>
              <p className="text-xs font-semibold uppercase tracking-wide">Temps restant</p>
              <p className="font-display text-2xl font-bold tabular-nums">{formatDuration(secondsLeft)}</p>
            </div>
          )}
        </div>

        <form onSubmit={submitAnswers} className="space-y-6">
          {questions.length === 0 ? (
            <div className="card text-center text-sm text-phila-gray-600">
              Aucune question disponible pour le moment. Revenez plus tard.
            </div>
          ) : (
            questions.map((question, index) => (
            <div key={question.id} className="card">
              <p className="mb-3 text-sm font-semibold text-phila-orange">Question {index + 1}</p>
              <p className="mb-4 font-medium">{question.stem}</p>

              {question.type === 'mcq' ? (
                <div className="space-y-2">
                  {question.options.map((option) => (
                    <label key={option.id} className="flex cursor-pointer items-center gap-3 rounded-lg border border-phila-gray-100 px-4 py-3 hover:bg-phila-orange-pale/30">
                      <input
                        type="radio"
                        name={`q-${question.id}`}
                        checked={answers[question.id]?.option_id === option.id}
                        onChange={() => setMcq(question.id, option.id)}
                        className="text-phila-orange"
                      />
                      <span className="text-sm">{option.label}</span>
                    </label>
                  ))}
                </div>
              ) : (
                <textarea
                  className="input-field min-h-[120px]"
                  placeholder="Votre réponse rédigée…"
                  value={answers[question.id]?.text ?? ''}
                  onChange={(e) => setWritten(question.id, e.target.value)}
                  required
                />
              )}
            </div>
            ))
          )}

          <LoadingButton
            type="submit"
            processing={form.processing}
            loadingText="Envoi…"
            disabled={questions.length === 0}
            className="btn btn-accent w-full"
          >
            Soumettre mes réponses
          </LoadingButton>

          {chapterId && (
            <Link href={`/mon-espace/cours/${chapterId}`} className="block text-center text-sm text-phila-gray-600 hover:underline">
              Annuler
            </Link>
          )}
        </form>
      </div>
    </div>
  );
}
