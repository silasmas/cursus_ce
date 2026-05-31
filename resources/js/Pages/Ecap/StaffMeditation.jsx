import { Head, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';
import FileAttachmentCard from '../../Components/UI/FileAttachmentCard';
import LoadingButton from '../../Components/UI/LoadingButton';
import UserAvatar from '../../Components/UI/UserAvatar';

/**
 * Modération des cahiers de méditation ECAP.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function StaffMeditation({
  templates = [],
  pending_submissions: pendingSubmissions = [],
  sessions = [],
  moderator_scope: moderatorScope = null,
}) {
  const { flash } = usePage().props;
  const form = useForm({
    academic_session_id: sessions[0]?.id ?? '',
    course_module_id: '',
    title: '',
    instructions: '',
    due_on: '',
    template_file: null,
  });

  const [reviewNotes, setReviewNotes] = useState({});
  const [reviewingId, setReviewingId] = useState(null);
  const [expandedTemplateId, setExpandedTemplateId] = useState(null);

  const handleCreate = (event) => {
    event.preventDefault();
    form.post('/ecap/acteurs/meditation/modeles', {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => form.reset('title', 'instructions', 'due_on', 'template_file'),
    });
  };

  const submitReview = async (submissionId, status) => {
    setReviewingId(submissionId);

    const formData = new FormData();
    formData.append('status', status);
    formData.append('moderator_notes', reviewNotes[submissionId] ?? '');

    try {
      await fetch(`/ecap/acteurs/meditation/remises/${submissionId}`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: formData,
      });

      window.location.reload();
    } finally {
      setReviewingId(null);
    }
  };

  return (
    <EcapStaffLayout active="meditation">
      <Head title="Cahiers méditation — Acteurs ECAP" />

      <div className="mx-auto max-w-4xl px-4 py-6">
        <h1 className="font-display text-2xl font-bold text-phila-black">Cahiers de méditation</h1>
        <p className="text-sm text-phila-gray-600">Publiez un modèle et corrigez les remises des fidèles.</p>

        {moderatorScope && (
          <p className="mt-2 rounded-xl bg-phila-orange-pale/50 px-4 py-2 text-xs text-phila-gray-700">
            Portée :{' '}
            {moderatorScope.covers_whole_session
              ? 'toute la session ECAP'
              : `vacation(s) ${moderatorScope.vacation_names?.join(', ') || 'assignée(s)'}`}
          </p>
        )}

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}

        <form onSubmit={handleCreate} className="mt-6 space-y-3 rounded-2xl border border-phila-gray-100 bg-white p-5 shadow-sm">
          <h2 className="font-display font-bold text-phila-black">Nouveau modèle</h2>
          <select
            className="w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
            value={form.data.academic_session_id}
            onChange={(event) => form.setData('academic_session_id', event.target.value)}
            required
          >
            {sessions.map((session) => (
              <option key={session.id} value={session.id}>
                {session.name}
              </option>
            ))}
          </select>
          <input
            type="text"
            placeholder="Titre du cahier"
            className="w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
            value={form.data.title}
            onChange={(event) => form.setData('title', event.target.value)}
            required
          />
          <textarea
            rows={4}
            placeholder="Consignes pour les fidèles"
            className="w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
            value={form.data.instructions}
            onChange={(event) => form.setData('instructions', event.target.value)}
          />
          <input
            type="date"
            className="w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
            value={form.data.due_on}
            onChange={(event) => form.setData('due_on', event.target.value)}
          />
          <input type="file" className="block w-full text-sm" onChange={(event) => form.setData('template_file', event.target.files?.[0] ?? null)} />
          <LoadingButton type="submit" processing={form.processing} loadingText="Publication…" className="btn btn-accent w-full py-2.5 text-sm">
            Publier le modèle
          </LoadingButton>
        </form>

        {pendingSubmissions.length > 0 && (
          <section className="mt-8">
            <h2 className="font-display text-lg font-bold">Remises à corriger ({pendingSubmissions.length})</h2>
            <ul className="mt-3 space-y-4">
              {pendingSubmissions.map((item) => (
                <SubmissionReviewCard
                  key={item.id}
                  item={item}
                  reviewNotes={reviewNotes}
                  setReviewNotes={setReviewNotes}
                  onReview={submitReview}
                  reviewingId={reviewingId}
                />
              ))}
            </ul>
          </section>
        )}

        {templates.length > 0 && (
          <section className="mt-8">
            <h2 className="font-display text-lg font-bold">Modèles publiés</h2>
            <ul className="mt-3 space-y-4">
              {templates.map((template) => (
                <li key={template.id} className="rounded-2xl border border-phila-gray-100 bg-white p-4 shadow-sm">
                  <div className="flex flex-wrap items-start justify-between gap-2">
                    <div>
                      <p className="font-semibold text-phila-black">{template.title}</p>
                      <p className="text-xs text-phila-gray-500">
                        {template.scope_label}
                        {template.due_on && ` · Échéance ${template.due_on}`}
                        {' · '}
                        {template.submissions_count} remise(s)
                      </p>
                    </div>
                    <button
                      type="button"
                      className="text-xs font-semibold text-phila-orange hover:underline"
                      onClick={() => setExpandedTemplateId(expandedTemplateId === template.id ? null : template.id)}
                    >
                      {expandedTemplateId === template.id ? 'Masquer les remises' : 'Voir les remises'}
                    </button>
                  </div>

                  {template.template_file_url && (
                    <div className="mt-3">
                      <FileAttachmentCard
                        url={template.template_file_url}
                        label="Modèle à télécharger"
                        fileName={template.template_file_name}
                      />
                    </div>
                  )}

                  {expandedTemplateId === template.id && template.submissions?.length > 0 && (
                    <ul className="mt-4 space-y-3 border-t border-phila-gray-100 pt-4">
                      {template.submissions.map((submission) => (
                        <li key={submission.id} className="rounded-xl bg-phila-gray-50 p-3">
                          <div className="flex items-center gap-3">
                            <UserAvatar
                              avatarUrl={submission.student_avatar_url}
                              name={submission.student_name}
                              sizeClass="h-9 w-9"
                              textClass="text-xs"
                            />
                            <div className="min-w-0 flex-1">
                              <p className="text-sm font-semibold text-phila-black">{submission.student_name}</p>
                              <p className="text-[10px] text-phila-gray-500">
                                {submission.submitted_at} · {submission.status}
                              </p>
                            </div>
                          </div>
                          {submission.answer_text && (
                            <p className="mt-2 whitespace-pre-wrap text-sm text-phila-gray-800">{submission.answer_text}</p>
                          )}
                          {submission.file_url && (
                            <div className="mt-2">
                              <FileAttachmentCard
                                url={submission.file_url}
                                label="Fichier remis"
                                fileName={submission.file_name}
                                subtitle={submission.submitted_at}
                              />
                            </div>
                          )}
                        </li>
                      ))}
                    </ul>
                  )}
                </li>
              ))}
            </ul>
          </section>
        )}
      </div>
    </EcapStaffLayout>
  );
}

/**
 * Carte de correction d'une remise en attente.
 */
function SubmissionReviewCard({ item, reviewNotes, setReviewNotes, onReview, reviewingId }) {
  const isReviewing = reviewingId === item.id;

  return (
    <li className="rounded-2xl border border-phila-orange/20 bg-white p-4 shadow-sm">
      <div className="flex items-center gap-3">
        <UserAvatar avatarUrl={item.student_avatar_url} name={item.student_name} sizeClass="h-10 w-10" textClass="text-xs" />
        <div className="min-w-0 flex-1">
          <p className="font-semibold text-phila-black">{item.student_name}</p>
          <p className="text-xs text-phila-gray-600">
            {item.template_title} · {item.template_scope} · {item.submitted_at}
          </p>
        </div>
      </div>

      {item.answer_text && (
        <p className="mt-3 whitespace-pre-wrap rounded-xl bg-phila-gray-50 px-3 py-2 text-sm text-phila-gray-800">{item.answer_text}</p>
      )}

      {item.file_url && (
        <div className="mt-3">
          <FileAttachmentCard url={item.file_url} label="Fichier remis" fileName={item.file_name} subtitle={item.submitted_at} />
        </div>
      )}

      <textarea
        rows={2}
        placeholder="Commentaire modérateur"
        className="mt-3 w-full rounded-lg border border-phila-gray-200 px-2 py-1.5 text-sm"
        value={reviewNotes[item.id] ?? ''}
        onChange={(event) => setReviewNotes((state) => ({ ...state, [item.id]: event.target.value }))}
      />
      <div className="mt-2 flex gap-2">
        <LoadingButton
          type="button"
          processing={isReviewing}
          loadingText="Envoi…"
          className="btn btn-accent px-4 py-1.5 text-xs"
          onClick={() => onReview(item.id, 'approved')}
        >
          Valider
        </LoadingButton>
        <LoadingButton
          type="button"
          processing={isReviewing}
          loadingText="Envoi…"
          className="btn btn-outline px-4 py-1.5 text-xs"
          onClick={() => onReview(item.id, 'rejected')}
        >
          Rejeter
        </LoadingButton>
      </div>
    </li>
  );
}
