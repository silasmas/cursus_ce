import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';

/**
 * Correction des TP remis par les fidèles (superviseur ECAP).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function StaffTpCorrections({ submissions = [] }) {
  const { flash } = usePage().props;
  const [gradingId, setGradingId] = useState(null);
  const [grade, setGrade] = useState('');
  const [notes, setNotes] = useState('');

  const submitGrade = async (submissionId) => {
    setGradingId(submissionId);

    try {
      await fetch(`/ecap/acteurs/corrections-tp/${submissionId}`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ grade: Number(grade), grader_notes: notes }),
      });

      router.reload({ only: ['submissions'], preserveScroll: true });
      setGrade('');
      setNotes('');
    } finally {
      setGradingId(null);
    }
  };

  return (
    <EcapStaffLayout active="corrections">
      <Head title="Corrections TP — Acteurs ECAP" />

      <div className="mx-auto max-w-3xl px-4 py-6">
        <h1 className="font-display text-2xl font-bold text-phila-black">Corrections TP</h1>
        <p className="mt-1 text-sm text-phila-gray-600">Remises des fidèles pour vos modules supervisés.</p>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}

        {submissions.length === 0 ? (
          <p className="mt-6 rounded-xl border border-dashed border-phila-gray-200 bg-white px-6 py-10 text-center text-sm text-phila-gray-500">
            Aucune remise en attente de correction.
          </p>
        ) : (
          <ul className="mt-6 space-y-4">
            {submissions.map((item) => (
              <li key={item.id} className="rounded-2xl border border-phila-gray-100 bg-white p-4 shadow-sm">
                <div className="flex flex-wrap items-start justify-between gap-2">
                  <div>
                    <p className="font-semibold text-phila-black">{item.student_name}</p>
                    <p className="text-xs text-phila-gray-600">
                      {item.tp_title} · {item.module_name}
                      {item.chapter_title && ` · ${item.chapter_title}`}
                    </p>
                    <p className="mt-1 text-[10px] text-phila-gray-400">Remis le {item.submitted_at}</p>
                  </div>
                </div>

                {item.answer_text && (
                  <p className="mt-3 rounded-xl bg-phila-gray-50 px-3 py-2 text-sm text-phila-gray-800">{item.answer_text}</p>
                )}

                {item.file_url && (
                  <a href={item.file_url} target="_blank" rel="noreferrer" className="mt-2 inline-block text-xs font-semibold text-phila-orange hover:underline">
                    Télécharger le fichier joint
                  </a>
                )}

                <div className="mt-4 flex flex-wrap items-end gap-3 border-t border-phila-gray-100 pt-3">
                  <div>
                    <label className="text-[10px] font-semibold uppercase text-phila-gray-500">Note /100</label>
                    <input
                      type="number"
                      min="0"
                      max="100"
                      className="mt-1 w-24 rounded-lg border border-phila-gray-200 px-2 py-1.5 text-sm"
                      value={grade}
                      onChange={(event) => setGrade(event.target.value)}
                    />
                  </div>
                  <div className="min-w-[200px] flex-1">
                    <label className="text-[10px] font-semibold uppercase text-phila-gray-500">Commentaire</label>
                    <input
                      type="text"
                      className="mt-1 w-full rounded-lg border border-phila-gray-200 px-2 py-1.5 text-sm"
                      value={notes}
                      onChange={(event) => setNotes(event.target.value)}
                    />
                  </div>
                  <button
                    type="button"
                    disabled={gradingId === item.id || grade === ''}
                    onClick={() => submitGrade(item.id)}
                    className="btn btn-accent px-4 py-2 text-xs"
                  >
                    Enregistrer
                  </button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </div>
    </EcapStaffLayout>
  );
}
