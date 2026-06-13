import { useEffect, useState } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import StarRating from '../UI/StarRating';
import LoadingButton from '../UI/LoadingButton';

/**
 * Modale de sondage de satisfaction fidèle (après X semaines d'inscription).
 *
 * @returns {JSX.Element|null}
 */
export default function MemberSurveyModal() {
  const page = usePage();
  const survey = page.props.memberSurvey;
  const [open, setOpen] = useState(Boolean(survey));

  const form = useForm({
    satisfaction: 0,
    nps_score: null,
    comment: '',
  });

  useEffect(() => {
    setOpen(Boolean(survey));
  }, [survey]);

  if (!open || !survey) {
    return null;
  }

  const handleSubmit = (event) => {
    event.preventDefault();

    if (form.data.satisfaction < 1) {
      return;
    }

    form.post(survey.submit_url, {
      preserveScroll: true,
      onSuccess: () => setOpen(false),
    });
  };

  const handleSnooze = () => {
    router.post(survey.snooze_url, {}, {
      preserveScroll: true,
      onSuccess: () => setOpen(false),
    });
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 p-4">
      <div
        className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="member-survey-title"
      >
        <h2 id="member-survey-title" className="text-xl font-bold text-phila-gray-900">
          Votre avis compte
        </h2>
        <p className="mt-2 text-sm text-phila-gray-600">
          Vous utilisez PHILA-CE depuis au moins {survey.weeks_after_enrollment} semaines.
          Quelques secondes pour nous dire comment se passe votre parcours ?
        </p>

        <form onSubmit={handleSubmit} className="mt-6 space-y-5">
          <div>
            <p className="mb-2 text-sm font-medium text-phila-gray-800">
              Globalement, comment évaluez-vous votre expérience ?
            </p>
            <StarRating
              value={form.data.satisfaction}
              onChange={(value) => form.setData('satisfaction', value)}
            />
            {form.errors.satisfaction && (
              <p className="mt-1 text-sm text-red-600">{form.errors.satisfaction}</p>
            )}
          </div>

          <div>
            <label htmlFor="member-survey-nps" className="mb-2 block text-sm font-medium text-phila-gray-800">
              Recommanderiez-vous PHILA-CE à un proche ? (0 = pas du tout, 10 = certainement)
            </label>
            <input
              id="member-survey-nps"
              type="range"
              min="0"
              max="10"
              value={form.data.nps_score ?? 5}
              onChange={(event) => form.setData('nps_score', Number(event.target.value))}
              className="w-full accent-phila-orange"
            />
            <p className="mt-1 text-sm text-phila-gray-600">
              Note : {form.data.nps_score ?? 5}/10
            </p>
          </div>

          <div>
            <label htmlFor="member-survey-comment" className="mb-2 block text-sm font-medium text-phila-gray-800">
              Commentaire (optionnel)
            </label>
            <textarea
              id="member-survey-comment"
              rows={3}
              value={form.data.comment}
              onChange={(event) => form.setData('comment', event.target.value)}
              className="w-full rounded-xl border border-phila-gray-200 px-3 py-2 text-sm focus:border-phila-orange focus:outline-none focus:ring-1 focus:ring-phila-orange"
              placeholder="Ce qui vous plaît, ce qui pourrait être amélioré…"
            />
          </div>

          <div className="flex flex-wrap items-center justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={handleSnooze}
              className="text-sm text-phila-gray-500 hover:text-phila-gray-800"
            >
              Plus tard
            </button>
            <LoadingButton
              type="submit"
              loading={form.processing}
              disabled={form.data.satisfaction < 1}
            >
              Envoyer mon avis
            </LoadingButton>
          </div>
        </form>
      </div>
    </div>
  );
}
