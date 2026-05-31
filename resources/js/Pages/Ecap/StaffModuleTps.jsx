import { Head, useForm, usePage } from '@inertiajs/react';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';

/**
 * Dépôt de TP modèle par l'enseignant ECAP.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function StaffModuleTps({ modules = [], tps = [] }) {
  const { flash } = usePage().props;
  const form = useForm({
    course_module_id: modules[0]?.id ?? '',
    chapter_id: '',
    title: '',
    instructions: '',
  });

  const selectedModule = modules.find((item) => item.id === Number(form.data.course_module_id));

  const handleSubmit = (event) => {
    event.preventDefault();
    form.post('/ecap/acteurs/tp', {
      preserveScroll: true,
      onSuccess: () => form.reset('title', 'instructions'),
    });
  };

  return (
    <EcapStaffLayout active="tps">
      <Head title="TP modèle — Acteurs ECAP" />

      <div className="mx-auto max-w-2xl px-4 py-6">
        <h1 className="font-display text-2xl font-bold text-phila-black">Déposer un TP modèle</h1>
        <p className="mt-1 text-sm text-phila-gray-600">Pour un module que vous enseignez — visible par les fidèles et le superviseur.</p>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}

        {modules.length === 0 ? (
          <p className="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Aucun module ne vous est affecté en tant qu&apos;enseignant.
          </p>
        ) : (
          <form onSubmit={handleSubmit} className="mt-6 space-y-4 rounded-2xl border border-phila-gray-100 bg-white p-5 shadow-sm">
            <div>
              <label className="text-xs font-semibold uppercase text-phila-gray-600">Module</label>
              <select
                className="mt-1 w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
                value={form.data.course_module_id}
                onChange={(event) => form.setData('course_module_id', event.target.value)}
              >
                {modules.map((module) => (
                  <option key={module.id} value={module.id}>
                    {module.name} — {module.course}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-xs font-semibold uppercase text-phila-gray-600">Chapitre (optionnel)</label>
              <select
                className="mt-1 w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
                value={form.data.chapter_id}
                onChange={(event) => form.setData('chapter_id', event.target.value)}
              >
                <option value="">Tout le module</option>
                {(selectedModule?.chapters ?? []).map((chapter) => (
                  <option key={chapter.id} value={chapter.id}>
                    {chapter.title}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-xs font-semibold uppercase text-phila-gray-600">Titre du TP</label>
              <input
                type="text"
                className="mt-1 w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
                value={form.data.title}
                onChange={(event) => form.setData('title', event.target.value)}
                required
              />
            </div>

            <div>
              <label className="text-xs font-semibold uppercase text-phila-gray-600">Consignes</label>
              <textarea
                rows={5}
                className="mt-1 w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm"
                value={form.data.instructions}
                onChange={(event) => form.setData('instructions', event.target.value)}
              />
            </div>

            <button type="submit" disabled={form.processing} className="btn btn-accent w-full py-2.5 text-sm">
              Publier le TP
            </button>
          </form>
        )}

        {tps.length > 0 && (
          <section className="mt-8">
            <h2 className="font-display text-lg font-bold">TP déjà publiés</h2>
            <ul className="mt-3 space-y-2">
              {tps.map((tp) => (
                <li key={tp.id} className="rounded-xl border border-phila-gray-100 bg-white px-4 py-3 text-sm">
                  <p className="font-semibold text-phila-black">{tp.title}</p>
                  <p className="text-xs text-phila-gray-600">
                    {tp.module_name}
                    {tp.chapter_title && ` · ${tp.chapter_title}`}
                    {' · '}
                    {tp.submissions_count} remise{tp.submissions_count > 1 ? 's' : ''}
                  </p>
                </li>
              ))}
            </ul>
          </section>
        )}
      </div>
    </EcapStaffLayout>
  );
}
