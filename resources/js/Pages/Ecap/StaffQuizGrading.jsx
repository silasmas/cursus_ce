import { Head, Link, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';
import { pollJson, startPolling } from '../../lib/pollJson';

/**
 * Liste des quiz en attente et historique (acteurs ECAP).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function StaffQuizGrading({
  attempts: initialAttempts = [],
  historyAttempts: initialHistoryAttempts = [],
  graderScope = '',
  feedUrl = null,
}) {
  const { flash } = usePage().props;
  const [attempts, setAttempts] = useState(initialAttempts);
  const [historyAttempts, setHistoryAttempts] = useState(initialHistoryAttempts);
  const [activeTab, setActiveTab] = useState(initialAttempts.length > 0 ? 'pending' : 'history');

  useEffect(() => {
    setAttempts(initialAttempts);
    setHistoryAttempts(initialHistoryAttempts);
  }, [initialAttempts, initialHistoryAttempts]);

  const refreshLists = useCallback(async () => {
    const data = await pollJson(feedUrl);

    if (!data) {
      return;
    }

    if (Array.isArray(data.attempts)) {
      setAttempts(data.attempts);
    }

    if (Array.isArray(data.historyAttempts)) {
      setHistoryAttempts(data.historyAttempts);
    }
  }, [feedUrl]);

  useEffect(() => {
    return startPolling(refreshLists, 10000, Boolean(feedUrl));
  }, [feedUrl, refreshLists]);

  return (
    <EcapStaffLayout active="quiz-grading">
      <Head title="Corrections quiz — Acteurs ECAP" />

      <div className="mx-auto max-w-3xl px-4 py-6">
        <h1 className="font-display text-2xl font-bold text-phila-black">Corrections quiz</h1>
        <p className="mt-1 text-sm text-phila-gray-600">
          Quiz avec réponses rédigées — en attente ou déjà corrigés dans votre périmètre.
        </p>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}

        {graderScope && (
          <p className="mt-2 rounded-xl border border-phila-gray-100 bg-white px-4 py-2 text-xs text-phila-gray-600">
            <span className="font-semibold text-phila-black">Votre périmètre :</span> {graderScope}
          </p>
        )}

        <div className="mt-4 flex gap-2">
          <button
            type="button"
            onClick={() => setActiveTab('pending')}
            className={`rounded-full px-4 py-1.5 text-xs font-semibold transition ${
              activeTab === 'pending'
                ? 'bg-phila-orange text-white'
                : 'bg-white text-phila-gray-600 ring-1 ring-phila-gray-200'
            }`}
          >
            En attente ({attempts.length})
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('history')}
            className={`rounded-full px-4 py-1.5 text-xs font-semibold transition ${
              activeTab === 'history'
                ? 'bg-phila-orange text-white'
                : 'bg-white text-phila-gray-600 ring-1 ring-phila-gray-200'
            }`}
          >
            Historique ({historyAttempts.length})
          </button>
        </div>

        {activeTab === 'pending' && attempts.length === 0 && (
          <div className="mt-6 space-y-3 rounded-xl border border-dashed border-phila-gray-200 bg-white px-6 py-10 text-center text-sm text-phila-gray-500">
            <p>Aucun quiz en attente de correction.</p>
            <p className="text-xs text-phila-gray-400">
              Seuls les quiz avec questions <strong className="text-phila-gray-600">rédigées</strong> apparaissent ici.
              Consultez l&apos;onglet <strong>Historique</strong> pour les corrections déjà enregistrées.
            </p>
          </div>
        )}

        {activeTab === 'pending' && attempts.length > 0 && (
          <ul className="mt-6 space-y-4">
            {attempts.map((item) => (
              <QuizListItem key={item.id} item={item} actionLabel={item.lock?.is_locked ? 'Voir (lecture seule)' : 'Corriger'} />
            ))}
          </ul>
        )}

        {activeTab === 'history' && historyAttempts.length === 0 && (
          <div className="mt-6 rounded-xl border border-dashed border-phila-gray-200 bg-white px-6 py-10 text-center text-sm text-phila-gray-500">
            Aucune correction enregistrée pour l&apos;instant.
          </div>
        )}

        {activeTab === 'history' && historyAttempts.length > 0 && (
          <ul className="mt-6 space-y-4">
            {historyAttempts.map((item) => (
              <QuizListItem key={item.id} item={item} actionLabel="Consulter / commenter" showGradeMeta />
            ))}
          </ul>
        )}
      </div>
    </EcapStaffLayout>
  );
}

/**
 * Carte d'une tentative dans la liste.
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
function QuizListItem({ item, actionLabel, showGradeMeta = false }) {
  return (
    <li className="rounded-2xl border border-phila-gray-100 bg-white p-4 shadow-sm">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <div className="flex flex-wrap items-center gap-2">
            <p className="font-semibold text-phila-black">{item.student_name}</p>
            <span
              className={`rounded-full px-2 py-0.5 text-[9px] font-bold uppercase ${
                showGradeMeta ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-900'
              }`}
            >
              {item.status_label}
            </span>
          </div>
          <p className="text-xs text-phila-gray-600">
            {item.assessment_title}
            {item.module_name && ` · ${item.module_name}`}
            {item.chapter_title && ` · ${item.chapter_title}`}
          </p>
          <p className="mt-1 text-[10px] text-phila-gray-400">Soumis le {item.submitted_at}</p>
          {showGradeMeta && (
            <p className="mt-1 text-[10px] text-phila-gray-500">
              Corrigé{item.graded_by_name ? ` par ${item.graded_by_name}` : ''}
              {item.graded_at ? ` le ${item.graded_at}` : ''}
              {item.score !== null && ` · ${item.score}%`}
            </p>
          )}
        </div>

        {item.lock?.is_locked && (
          <span className="rounded-full bg-amber-100 px-3 py-1 text-[10px] font-semibold text-amber-900">
            En correction par {item.lock.locked_by?.name}
          </span>
        )}
      </div>

      <Link
        href={`/ecap/acteurs/corrections-quiz/${item.id}`}
        className="mt-3 inline-flex text-xs font-semibold text-phila-orange hover:underline"
      >
        {actionLabel}
      </Link>
    </li>
  );
}
