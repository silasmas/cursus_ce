import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/Layout/AppLayout';
import AppointmentCard from '../../Components/UI/AppointmentCard';
import FloatingMentorChat from '../../Components/UI/FloatingMentorChat';
import LoadingButton from '../../Components/UI/LoadingButton';
import StarRating from '../../Components/UI/StarRating';
import UserAvatar from '../../Components/UI/UserAvatar';

/**
 * Vue mentoré — profil mentor, RDV, chat flottant et rapport de progression.
 */
export default function MenteeView({
  mentor,
  messages,
  chatEnabled,
  chatPollUrl,
  chatSendUrl,
  accompanimentClosed = false,
  closedAt = null,
  canSubmitClosureFeedback = false,
  hasSubmittedClosureFeedback = false,
  hasSubmittedFeedback,
  reportUnlocked,
  reportBlockReason,
  appointments = [],
  feedbacks,
}) {
  const { auth, flash } = usePage().props;
  const feedbackForm = useForm({ rating: 5, comment: '' });
  const closureFeedbackForm = useForm({ rating: 5, comment: '' });

  const sendFeedback = (event) => {
    event.preventDefault();
    feedbackForm.post('/mon-espace/mentor/avis', { preserveScroll: true });
  };

  const sendClosureFeedback = (event) => {
    event.preventDefault();
    closureFeedbackForm.post('/mon-espace/mentor/avis-cloture', { preserveScroll: true });
  };

  return (
    <AppLayout user={auth.user}>
      <Head title="Mon mentor" />
      <div className="container-phila py-10 pb-24">
        <Link href="/mon-espace?cursus=metamorpho" className="text-sm text-phila-orange hover:underline">
          ← Retour Métamorpho
        </Link>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{flash.status}</div>
        )}
        {flash?.error && (
          <div className="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{flash.error}</div>
        )}

        <div className="mt-6 grid gap-8 lg:grid-cols-2">
          <div className="card space-y-4">
            <h1 className="font-display text-2xl font-bold">Profil de votre mentor</h1>
            <div className="flex items-center gap-4">
              <UserAvatar
                avatarUrl={mentor.avatar_url}
                name={mentor.name}
                initials={mentor.initials}
                size="lg"
              />
              <div>
                <p className="font-display text-xl font-bold">{mentor.name}</p>
                {mentor.gender && (
                  <p className="text-sm text-phila-gray-600">{mentor.gender}</p>
                )}
                {mentor.started_at && (
                  <p className="mt-1 text-xs text-phila-gray-500">Accompagnement depuis le {mentor.started_at}</p>
                )}
              </div>
            </div>
          </div>

          <div className="card space-y-4">
            <h2 className="font-display text-lg font-bold">Mes rendez-vous</h2>
            {appointments.length === 0 ? (
              <p className="text-sm text-phila-gray-600">Aucun rendez-vous programmé pour le moment.</p>
            ) : (
              <ul className="space-y-3">
                {appointments.map((appt) => (
                  <AppointmentCard key={appt.id} appointment={appt} canRespond />
                ))}
              </ul>
            )}
          </div>
        </div>

        {accompanimentClosed && (
          <div className="card mt-8 space-y-4 border-phila-orange/30 bg-phila-orange-pale/30">
            <h2 className="font-display text-lg font-bold">Accompagnement clôturé</h2>
            <p className="text-sm text-phila-gray-700">
              Votre mentor a clôturé votre accompagnement
              {closedAt ? ` le ${closedAt}` : ''}. Merci pour votre participation à ce parcours.
            </p>

            {canSubmitClosureFeedback ? (
              <form onSubmit={sendClosureFeedback} className="space-y-4">
                <p className="text-sm text-phila-gray-600">
                  Partagez votre avis sur cette expérience de mentorat.
                </p>
                <div>
                  <label className="label-field">Votre évaluation</label>
                  <StarRating
                    value={closureFeedbackForm.data.rating}
                    onChange={(rating) => closureFeedbackForm.setData('rating', rating)}
                  />
                </div>
                <div>
                  <label className="label-field">Votre avis</label>
                  <textarea
                    className="input-field min-h-[120px]"
                    value={closureFeedbackForm.data.comment}
                    onChange={(e) => closureFeedbackForm.setData('comment', e.target.value)}
                    placeholder="Qu'avez-vous retiré de cet accompagnement ?"
                    required
                  />
                </div>
                <LoadingButton
                  type="submit"
                  processing={closureFeedbackForm.processing}
                  loadingText="Envoi…"
                  className="btn btn-accent"
                >
                  Envoyer mon avis
                </LoadingButton>
              </form>
            ) : hasSubmittedClosureFeedback ? (
              <p className="text-sm text-green-700">Merci, votre avis a bien été enregistré.</p>
            ) : null}
          </div>
        )}

        {!accompanimentClosed && (
        <div className="card mt-8 space-y-4">
          <h2 className="font-display text-lg font-bold">Rapport de progression</h2>
          {reportUnlocked ? (
            <form onSubmit={sendFeedback} className="space-y-4">
              <p className="text-sm text-phila-gray-600">
                Votre mentor a validé votre progression. Complétez ce rapport pour passer au niveau suivant.
              </p>
              <div>
                <label className="label-field">Votre évaluation</label>
                <StarRating
                  value={feedbackForm.data.rating}
                  onChange={(rating) => feedbackForm.setData('rating', rating)}
                />
              </div>
              <div>
                <label className="label-field">Commentaire</label>
                <textarea
                  className="input-field min-h-[120px]"
                  value={feedbackForm.data.comment}
                  onChange={(e) => feedbackForm.setData('comment', e.target.value)}
                  placeholder="Décrivez votre progression et ce que vous avez appris…"
                  required
                />
              </div>
              <LoadingButton
                type="submit"
                processing={feedbackForm.processing}
                loadingText="Envoi du rapport…"
                className="btn btn-accent"
              >
                Soumettre mon rapport
              </LoadingButton>
            </form>
          ) : hasSubmittedFeedback && feedbacks?.length > 0 ? (
            <div className="space-y-2">
              <p className="text-sm text-green-700">Rapport soumis — en attente de la suite du parcours.</p>
              {feedbacks.map((fb, i) => (
                <div key={i} className="rounded-xl bg-phila-gray-50 p-4 text-sm">
                  <StarRating value={fb.rating} onChange={() => {}} disabled />
                  <p className="mt-2">{fb.body}</p>
                  <p className="mt-1 text-xs text-phila-gray-500">{fb.created_at}</p>
                </div>
              ))}
            </div>
          ) : (
            <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
              <p className="font-semibold">Formulaire verrouillé</p>
              <p className="mt-1">{reportBlockReason ?? 'Attendez l\'aval de votre mentor pour débloquer ce rapport.'}</p>
            </div>
          )}
        </div>
        )}
      </div>

      <FloatingMentorChat
        initialMessages={messages}
        pollUrl={chatPollUrl}
        sendUrl={chatSendUrl}
        enabled={chatEnabled}
        title={`Chat avec ${mentor?.name ?? 'votre mentor'}`}
        placeholder="Écrire à votre mentor…"
      />
    </AppLayout>
  );
}
