import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import MentorLayout from '../../Components/Layout/MentorLayout';
import AppointmentCard from '../../Components/UI/AppointmentCard';
import FloatingMentorChat from '../../Components/UI/FloatingMentorChat';
import LoadingButton from '../../Components/UI/LoadingButton';
import UserAvatar from '../../Components/UI/UserAvatar';

/**
 * Carte TP remis par le mentor avec modification si en attente admin.
 */
function MentorSubmissionCard({ submission, assignmentId }) {
  const [editing, setEditing] = useState(false);
  const form = useForm({ answer_text: submission.answer_text ?? '', file: null });

  const save = (event) => {
    event.preventDefault();
    form.patch(`/mentor/mentore/${assignmentId}/tp/${submission.id}`, {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => setEditing(false),
    });
  };

  return (
    <div className="rounded-xl border border-phila-gray-100 p-4 text-sm">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <div>
          <p className="font-semibold">{submission.title}</p>
          <p className="text-xs text-phila-gray-500">{submission.chapter} · {submission.submitted_at}</p>
        </div>
        <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-semibold ${
          submission.admin_publication_status === 'pending_review'
            ? 'bg-amber-100 text-amber-800'
            : submission.admin_publication_status === 'published'
              ? 'bg-green-100 text-green-800'
              : 'bg-red-100 text-red-800'
        }`}>
          {submission.admin_publication_status === 'pending_review'
            ? 'En attente admin'
            : submission.admin_publication_status === 'published'
              ? 'Visible mentoré'
              : 'Refusé'}
        </span>
      </div>
      {!editing && submission.answer_text && (
        <p className="mt-2 line-clamp-3 rounded-lg bg-phila-gray-50 p-2 text-xs">{submission.answer_text}</p>
      )}
      {submission.can_edit && (
        <div className="mt-3">
          {!editing ? (
            <button type="button" onClick={() => setEditing(true)} className="text-xs font-semibold text-phila-orange hover:underline">
              Modifier ce TP
            </button>
          ) : (
            <form onSubmit={save} className="mt-2 space-y-2">
              <textarea
                className="input-field min-h-[80px] text-sm"
                value={form.data.answer_text}
                onChange={(e) => form.setData('answer_text', e.target.value)}
              />
              <input type="file" className="text-xs" onChange={(e) => form.setData('file', e.target.files[0])} />
              <div className="flex gap-2">
                <LoadingButton type="submit" processing={form.processing} loadingText="Enregistrement…" className="btn btn-accent px-3 py-1.5 text-xs">
                  Enregistrer
                </LoadingButton>
                <button type="button" onClick={() => setEditing(false)} className="btn btn-outline px-3 py-1.5 text-xs">Annuler</button>
              </div>
            </form>
          )}
        </div>
      )}
    </div>
  );
}

/**
 * Fiche mentoré — profil, suivi des TP/RDV, chat (formulaires centralisés ailleurs).
 */
export default function MenteeDetail({
  mentee,
  assignmentId,
  messages,
  chatPollUrl,
  chatSendUrl,
  feedbacks,
  submissions = [],
  mentorSubmissions = [],
  appointments = [],
  assignmentStatus = 'active',
}) {
  const { flash } = usePage().props;

  const whatsappUrl = mentee.phone
    ? `https://wa.me/${mentee.phone.replace(/\D/g, '')}`
    : null;

  return (
    <MentorLayout active="dashboard">
      <Head title={mentee.name} />
      <div className="container-phila py-10 pb-24">
        <Link href="/mentor/mentores" className="text-sm text-phila-orange hover:underline">← Mes mentorés</Link>

        {assignmentStatus === 'closed' && (
          <div className="mt-4 rounded-xl border border-phila-orange/30 bg-phila-orange-pale/40 px-4 py-3 text-sm">
            Accompagnement <strong>clôturé</strong>.
          </div>
        )}

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{flash.status}</div>
        )}
        {flash?.error && (
          <div className="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{flash.error}</div>
        )}

        <div className="mt-6 card">
          <p className="text-[10px] font-semibold uppercase tracking-wider text-phila-orange">Profil mentoré</p>
          <div className="mt-4 flex flex-wrap items-start gap-6">
            <UserAvatar
              avatarUrl={mentee.avatar_url}
              name={mentee.name}
              initials={mentee.initials}
              size="xl"
            />
            <div className="min-w-0 flex-1 space-y-2">
              <h1 className="font-display text-2xl font-bold">{mentee.name}</h1>
              <div className="grid gap-2 text-sm sm:grid-cols-2">
                {mentee.gender && <p><strong>Sexe :</strong> {mentee.gender}</p>}
                {mentee.age != null && <p><strong>Âge :</strong> {mentee.age} ans</p>}
                {mentee.country && <p><strong>Pays :</strong> {mentee.country}</p>}
                {mentee.program && <p><strong>Programme :</strong> {mentee.program}</p>}
                {mentee.started_at && <p><strong>Depuis :</strong> {mentee.started_at}</p>}
                <p><strong>E-mail :</strong> {mentee.email}</p>
                {mentee.phone && <p><strong>Téléphone :</strong> {mentee.phone}</p>}
              </div>
              <div className="flex flex-wrap gap-2 pt-2">
                {whatsappUrl && (
                  <a href={whatsappUrl} target="_blank" rel="noopener noreferrer" className="btn btn-accent text-sm">
                    WhatsApp
                  </a>
                )}
                {mentee.phone && (
                  <a href={`tel:${mentee.phone}`} className="btn btn-outline text-sm">Appeler</a>
                )}
              </div>
            </div>
          </div>
        </div>

        {assignmentStatus === 'active' && (
          <div className="card mt-8">
            <h2 className="font-display text-lg font-bold">Actions</h2>
            <p className="mt-1 text-sm text-phila-gray-600">
              Les formulaires de rendez-vous et de remise de TP sont centralisés dans l&apos;espace dédié.
            </p>
            <div className="mt-4 flex flex-wrap gap-2">
              <Link href="/mentor/formulaires#rdv" className="btn btn-accent text-sm">
                Programmer un RDV
              </Link>
              <Link href="/mentor/formulaires#tp" className="btn btn-outline text-sm">
                Remettre un TP
              </Link>
              <Link href="/mentor/formulaires#cloture" className="btn btn-outline text-sm">
                Clôturer l&apos;accompagnement
              </Link>
            </div>
          </div>
        )}

        {appointments.length > 0 && (
          <div className="card mt-8 space-y-4">
            <h2 className="font-display text-lg font-bold">Rendez-vous</h2>
            <ul className="space-y-3">
              {appointments.map((appt) => (
                <AppointmentCard key={appt.id} appointment={appt} canEdit={assignmentStatus === 'active'} />
              ))}
            </ul>
          </div>
        )}

        {mentorSubmissions.length > 0 && (
          <div className="card mt-8">
            <h2 className="font-display text-lg font-bold">TP remis par vous ({mentorSubmissions.length})</h2>
            <div className="mt-4 space-y-3">
              {mentorSubmissions.map((submission) => (
                <MentorSubmissionCard
                  key={submission.id}
                  submission={submission}
                  assignmentId={assignmentId}
                />
              ))}
            </div>
          </div>
        )}

        <div className="card mt-8">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h2 className="font-display text-lg font-bold">TP du mentoré à corriger ({submissions.length})</h2>
            <Link href="/mentor/soumissions" className="text-sm font-medium text-phila-orange hover:underline">
              Voir toutes les soumissions →
            </Link>
          </div>
          {submissions.length === 0 ? (
            <p className="mt-4 text-sm text-phila-gray-600">Aucun TP remis par le mentoré pour le moment.</p>
          ) : (
            <div className="mt-4 space-y-3">
              {submissions.map((submission) => (
                <div key={submission.id} className="rounded-xl border border-phila-gray-100 p-4 text-sm">
                  <div className="flex flex-wrap items-start justify-between gap-2">
                    <div>
                      <p className="font-semibold">{submission.title}</p>
                      <p className="text-xs text-phila-gray-500">{submission.chapter} · {submission.submitted_at}</p>
                    </div>
                    <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-semibold ${
                      submission.mentor_status === 'pending'
                        ? 'bg-amber-100 text-amber-800'
                        : submission.mentor_status === 'approved'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                    }`}>
                      {submission.mentor_status === 'pending' ? 'En attente' : submission.mentor_status === 'approved' ? 'Validé' : 'Refusé'}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {feedbacks.length > 0 && (
          <div className="card mt-8">
            <h2 className="font-display text-lg font-bold">Rapports / avis reçus</h2>
            <div className="mt-4 space-y-3">
              {feedbacks.map((fb, i) => (
                <div key={i} className="rounded-xl bg-phila-gray-50 p-4 text-sm">
                  {fb.rating && <p className="text-phila-orange">{'★'.repeat(fb.rating)}</p>}
                  <p className="mt-2">{fb.body}</p>
                  <p className="mt-1 text-xs text-phila-gray-500">
                    {fb.type_label ?? fb.type} · {fb.author} · {fb.created_at}
                  </p>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      <FloatingMentorChat
        initialMessages={messages}
        pollUrl={chatPollUrl}
        sendUrl={chatSendUrl}
        enabled={assignmentStatus === 'active'}
        title={`Chat avec ${mentee.name}`}
        placeholder="Message au mentoré…"
      />
    </MentorLayout>
  );
}
