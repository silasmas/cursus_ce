import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/Layout/AppLayout';
import FileAttachmentCard from '../../Components/UI/FileAttachmentCard';
import LoadingButton from '../../Components/UI/LoadingButton';

/**
 * Cahiers de méditation ECAP — remise fidèle.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function MeditationNotebooks({ templates = [] }) {
  const { flash } = usePage().props;

  return (
    <AppLayout>
      <Head title="Cahiers de méditation ECAP" />

      <div className="container-phila py-8">
        <Link href="/mon-espace" className="text-sm text-phila-orange hover:underline">
          ← Mon espace
        </Link>
        <h1 className="mt-2 font-display text-2xl font-bold text-phila-black">Cahiers de méditation</h1>
        <p className="text-sm text-phila-gray-600">Téléchargez le modèle publié par votre modérateur et remettez votre travail.</p>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}

        {templates.length === 0 ? (
          <p className="mt-8 rounded-xl border border-dashed border-phila-gray-200 bg-white px-6 py-10 text-center text-sm text-phila-gray-500">
            Aucun cahier publié pour votre session ou vacation pour le moment.
          </p>
        ) : (
          <ul className="mt-8 space-y-6">
            {templates.map((template) => (
              <MeditationCard key={template.id} template={template} />
            ))}
          </ul>
        )}
      </div>
    </AppLayout>
  );
}

/**
 * Carte d'un cahier avec formulaire de remise.
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
function MeditationCard({ template }) {
  const form = useForm({
    answer_text: template.submission?.answer_text ?? '',
    work_file: null,
  });

  const handleSubmit = (event) => {
    event.preventDefault();
    form.post(`/mon-espace/ecap/meditation/${template.id}`, {
      forceFormData: true,
      preserveScroll: true,
    });
  };

  const statusLabel = {
    submitted: 'Remis — en attente de correction',
    approved: 'Validé par le modérateur',
    rejected: 'À retravailler',
  };

  const statusClass = {
    submitted: 'bg-amber-100 text-amber-900',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };

  return (
    <li className="rounded-2xl border border-phila-gray-100 bg-white p-5 shadow-sm">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <div>
          <h2 className="font-display text-lg font-bold text-phila-black">{template.title}</h2>
          {template.module_name && <p className="text-xs text-phila-gray-600">Module : {template.module_name}</p>}
          <p className="text-xs text-phila-gray-500">Portée : {template.scope_label}</p>
          {template.due_on && <p className="text-xs text-phila-orange">À remettre avant le {template.due_on}</p>}
        </div>
        {template.submission?.status && (
          <span className={`rounded-full px-3 py-1 text-[10px] font-bold uppercase ${statusClass[template.submission.status] ?? 'bg-phila-orange-pale text-phila-orange'}`}>
            {statusLabel[template.submission.status] ?? template.submission.status}
          </span>
        )}
      </div>

      {template.instructions && (
        <p className="mt-3 whitespace-pre-wrap rounded-xl bg-phila-gray-50 px-4 py-3 text-sm text-phila-gray-800">
          {template.instructions}
        </p>
      )}

      {template.template_file_url && (
        <div className="mt-3">
          <FileAttachmentCard
            url={template.template_file_url}
            label="Modèle du cahier"
            fileName={template.template_file_name}
          />
        </div>
      )}

      {template.submission?.file_url && (
        <div className="mt-3">
          <p className="mb-2 text-xs font-semibold uppercase text-phila-gray-600">Votre remise</p>
          <FileAttachmentCard
            url={template.submission.file_url}
            label="Fichier envoyé"
            fileName={template.submission.file_name}
            subtitle={template.submission.submitted_at ? `Remis le ${template.submission.submitted_at}` : null}
          />
        </div>
      )}

      {template.submission?.moderator_notes && (
        <p className="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          <strong>Retour modérateur :</strong> {template.submission.moderator_notes}
        </p>
      )}

      <form onSubmit={handleSubmit} className="mt-4 space-y-3 border-t border-phila-gray-100 pt-4">
        <div>
          <label className="text-xs font-semibold uppercase text-phila-gray-600">Votre travail</label>
          <textarea
            rows={6}
            required
            className="mt-1 w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
            value={form.data.answer_text}
            onChange={(event) => form.setData('answer_text', event.target.value)}
          />
        </div>
        <div>
          <label className="text-xs font-semibold uppercase text-phila-gray-600">Fichier joint (optionnel)</label>
          <input
            type="file"
            className="mt-1 block w-full text-sm"
            onChange={(event) => form.setData('work_file', event.target.files?.[0] ?? null)}
          />
        </div>
        <LoadingButton type="submit" processing={form.processing} loadingText="Envoi…" className="btn btn-accent px-5 py-2 text-sm">
          Remettre au modérateur
        </LoadingButton>
      </form>
    </li>
  );
}
