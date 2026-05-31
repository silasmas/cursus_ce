import { Head, Link, useForm, usePage } from '@inertiajs/react';
import MentorLayout from '../../Components/Layout/MentorLayout';
import LoadingButton from '../../Components/UI/LoadingButton';

/**
 * Carte de correction d'une soumission mentoré.
 */
function SubmissionReviewCard({ submission }) {
  const form = useForm({ decision: 'approved', notes: '' });

  const submitReview = (decision) => {
    form.setData('decision', decision);
    form.post(`/mentor/soumissions/${submission.id}/valider`, {
      preserveScroll: true,
    });
  };

  return (
    <div className="card space-y-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <p className="font-display font-bold">{submission.title}</p>
          <p className="text-sm text-phila-gray-600">
            {submission.mentee_name} · {submission.program} · {submission.chapter}
          </p>
          <p className="text-xs text-phila-gray-500">Remis le {submission.submitted_at}</p>
        </div>
        <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">En attente mentor</span>
      </div>

      {submission.answer_text && (
        <div className="rounded-xl bg-phila-gray-50 p-4 text-sm whitespace-pre-wrap">{submission.answer_text}</div>
      )}

      {submission.file_url && (
        <a href={submission.file_url} target="_blank" rel="noopener noreferrer" className="text-sm font-medium text-phila-orange hover:underline">
          Télécharger le fichier remis
        </a>
      )}

      <div>
        <label className="label-field">Votre avis (transmis à l&apos;administration)</label>
        <textarea
          className="input-field min-h-[100px]"
          placeholder="Commentaire pour le mentoré et l'équipe pédagogique…"
          value={form.data.notes}
          onChange={(e) => form.setData('notes', e.target.value)}
        />
        {form.errors.notes && <p className="mt-1 text-sm text-red-600">{form.errors.notes}</p>}
      </div>

      <div className="flex flex-wrap gap-2">
        <LoadingButton
          type="button"
          processing={form.processing}
          loadingText="Validation…"
          disabled={form.data.notes.length < 10}
          onClick={() => submitReview('approved')}
          className="btn btn-accent text-sm"
        >
          Valider — autoriser la progression
        </LoadingButton>
        <LoadingButton
          type="button"
          processing={form.processing}
          loadingText="Traitement…"
          disabled={form.data.notes.length < 10}
          onClick={() => submitReview('rejected')}
          className="btn btn-outline text-sm text-red-700"
        >
          Refuser — demander une correction
        </LoadingButton>
      </div>
    </div>
  );
}

/**
 * Liste des TP soumis par les mentorés en attente de validation.
 */
export default function Submissions({ submissions, pendingCount }) {
  const { flash } = usePage().props;

  return (
    <MentorLayout active="submissions">
      <Head title="Soumissions à corriger" />
      <div className="container-phila py-10">
        <h1 className="font-display text-2xl font-bold">Soumissions à corriger</h1>
        <p className="mt-2 text-sm text-phila-gray-600">
          Validez les travaux de vos mentorés. Sans votre aval, ils ne peuvent pas passer au niveau suivant.
        </p>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{flash.status}</div>
        )}
        {flash?.error && (
          <div className="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{flash.error}</div>
        )}

        {submissions.length === 0 ? (
          <div className="card mt-8 text-center text-sm text-phila-gray-600">Aucune soumission en attente ({pendingCount}).</div>
        ) : (
          <div className="mt-8 space-y-6">
            {submissions.map((submission) => (
              <SubmissionReviewCard key={submission.id} submission={submission} />
            ))}
          </div>
        )}
      </div>
    </MentorLayout>
  );
}
