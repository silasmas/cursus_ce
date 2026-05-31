import { router, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import {
  formatAppointmentCountdown,
  formatLocalDateTime,
  getAppointmentPhase,
  getPhaseBadge,
  isoToLocalInput,
  localDateTimeToIso,
} from '../../utils/appointmentTime';
import DateTimePicker from './DateTimePicker';
import LoadingButton from './LoadingButton';

const responseBadges = {
  pending: { label: 'Réponse attendue', className: 'bg-phila-gray-100 text-phila-gray-700' },
  accepted: { label: 'Accepté', className: 'bg-green-100 text-green-800' },
  declined: { label: 'Refusé', className: 'bg-red-100 text-red-800' },
  postponed: { label: 'Reporté', className: 'bg-amber-100 text-amber-900' },
};

/**
 * Carte rendez-vous avec badge temporel, compte à rebours et réactions mentoré.
 *
 * @param {Object} props
 * @param {Object} props.appointment Données du RDV
 * @param {boolean} [props.canRespond=false] Afficher les actions mentoré
 * @param {boolean} [props.canEdit=false] Afficher modification mentor
 * @returns {JSX.Element}
 */
export default function AppointmentCard({ appointment, canRespond = false, canEdit = false }) {
  const [countdown, setCountdown] = useState(formatAppointmentCountdown(appointment.scheduled_at_iso));
  const [phase, setPhase] = useState(getAppointmentPhase(appointment.scheduled_at_iso));
  const [showPostpone, setShowPostpone] = useState(false);
  const [showEdit, setShowEdit] = useState(false);
  const [proposedAt, setProposedAt] = useState('');
  const [responseNote, setResponseNote] = useState('');
  const [processing, setProcessing] = useState(false);
  const editForm = useForm({
    scheduled_at: isoToLocalInput(appointment.scheduled_at_iso),
    channel: appointment.channel,
    meeting_url: appointment.meeting_url ?? '',
    notes: appointment.notes ?? '',
  });

  useEffect(() => {
    const tick = () => {
      setCountdown(formatAppointmentCountdown(appointment.scheduled_at_iso));
      setPhase(getAppointmentPhase(appointment.scheduled_at_iso));
    };

    tick();
    const interval = setInterval(tick, phase === 'soon' || phase === 'ongoing' ? 1000 : 30000);

    return () => clearInterval(interval);
  }, [appointment.scheduled_at_iso, phase]);

  const phaseBadge = getPhaseBadge(phase);
  const responseBadge = responseBadges[appointment.mentee_response] ?? responseBadges.pending;

  const submitResponse = async (response) => {
    if (response === 'postponed' && !proposedAt) {
      setShowPostpone(true);
      return;
    }

    setProcessing(true);

    try {
      const payload = {
        response,
        response_note: responseNote || null,
        proposed_reschedule_at: response === 'postponed' ? localDateTimeToIso(proposedAt) : null,
      };

      await fetch(`/mon-espace/mentor/rendez-vous/${appointment.id}/reponse`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      });

      router.reload({ only: ['appointments'], preserveScroll: true });
    } finally {
      setProcessing(false);
    }
  };

  const canStillRespond = canRespond
    && appointment.mentee_response === 'pending'
    && phase !== 'past';

  return (
    <li className="rounded-xl border border-phila-gray-100 p-4 text-sm">
      <div className="flex flex-wrap items-start justify-between gap-2">
        <div className="flex flex-wrap gap-2">
          <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-semibold ${phaseBadge.className}`}>
            {phaseBadge.label}
          </span>
          <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-semibold ${responseBadge.className}`}>
            {responseBadge.label}
          </span>
        </div>
        <span className="text-xs font-semibold text-phila-orange">{countdown}</span>
      </div>

      <p className="mt-2 font-semibold">
        {formatLocalDateTime(appointment.scheduled_at_iso)} — {appointment.channel_label}
      </p>

      {appointment.mentee_name && (
        <p className="text-xs text-phila-gray-500">Avec {appointment.mentee_name}</p>
      )}

      {appointment.meeting_url && phase !== 'past' && (
        <a
          href={appointment.meeting_url}
          target="_blank"
          rel="noopener noreferrer"
          className="mt-2 inline-block text-xs font-medium text-phila-orange hover:underline"
        >
          Rejoindre la réunion →
        </a>
      )}

      {appointment.notes && (
        <p className="mt-2 text-xs text-phila-gray-600">{appointment.notes}</p>
      )}

      {appointment.proposed_reschedule_at_iso && (
        <p className="mt-2 text-xs text-amber-800">
          Nouvelle date proposée : {formatLocalDateTime(appointment.proposed_reschedule_at_iso)}
        </p>
      )}

      {canStillRespond && (
        <div className="mt-4 space-y-3 border-t border-phila-gray-100 pt-3">
          <p className="text-xs font-semibold text-phila-gray-700">Votre réponse :</p>
          <div className="flex flex-wrap gap-2">
            <LoadingButton
              type="button"
              processing={processing}
              loadingText="Envoi…"
              onClick={() => submitResponse('accepted')}
              className="btn btn-accent px-3 py-1.5 text-xs"
            >
              Accepter
            </LoadingButton>
            <LoadingButton
              type="button"
              processing={processing}
              loadingText="Envoi…"
              onClick={() => submitResponse('declined')}
              className="btn btn-outline px-3 py-1.5 text-xs text-red-700"
            >
              Refuser
            </LoadingButton>
            <LoadingButton
              type="button"
              processing={processing}
              loadingText="Envoi…"
              onClick={() => setShowPostpone((v) => !v)}
              className="btn btn-outline px-3 py-1.5 text-xs"
            >
              Reporter
            </LoadingButton>
          </div>

          {showPostpone && (
            <div className="space-y-2 rounded-xl bg-phila-gray-50 p-3">
              <DateTimePicker
                label="Date proposée"
                value={proposedAt}
                onChange={setProposedAt}
                required
              />
              <textarea
                className="input-field min-h-[60px] text-xs"
                placeholder="Motif du report (optionnel)…"
                value={responseNote}
                onChange={(e) => setResponseNote(e.target.value)}
              />
              <LoadingButton
                type="button"
                processing={processing}
                loadingText="Envoi…"
                disabled={!proposedAt}
                onClick={() => submitResponse('postponed')}
                className="btn btn-accent px-3 py-1.5 text-xs"
              >
                Envoyer la demande de report
              </LoadingButton>
            </div>
          )}
        </div>
      )}

      {canEdit && appointment.can_edit !== false && phase !== 'past' && (
        <div className="mt-4 border-t border-phila-gray-100 pt-3">
          <button
            type="button"
            onClick={() => setShowEdit((v) => !v)}
            className="text-xs font-semibold text-phila-orange hover:underline"
          >
            {showEdit ? 'Annuler la modification' : 'Modifier ce rendez-vous'}
          </button>
          {showEdit && (
            <form
              className="mt-3 space-y-3"
              onSubmit={(e) => {
                e.preventDefault();
                editForm.transform((data) => ({
                  ...data,
                  scheduled_at: localDateTimeToIso(data.scheduled_at),
                }));
                editForm.patch(`/mentor/rendez-vous/${appointment.id}`, {
                  preserveScroll: true,
                  onSuccess: () => setShowEdit(false),
                });
              }}
            >
              <DateTimePicker
                value={editForm.data.scheduled_at}
                onChange={(v) => editForm.setData('scheduled_at', v)}
                required
              />
              <select
                className="input-field text-sm"
                value={editForm.data.channel}
                onChange={(e) => editForm.setData('channel', e.target.value)}
              >
                <option value="whatsapp">WhatsApp</option>
                <option value="zoom">Zoom</option>
                <option value="google_meet">Google Meet</option>
              </select>
              <input
                className="input-field text-sm"
                placeholder="Lien de réunion"
                value={editForm.data.meeting_url}
                onChange={(e) => editForm.setData('meeting_url', e.target.value)}
              />
              <textarea
                className="input-field min-h-[60px] text-sm"
                value={editForm.data.notes}
                onChange={(e) => editForm.setData('notes', e.target.value)}
              />
              <LoadingButton
                type="submit"
                processing={editForm.processing}
                loadingText="Enregistrement…"
                className="btn btn-accent px-3 py-1.5 text-xs"
              >
                Enregistrer les modifications
              </LoadingButton>
            </form>
          )}
        </div>
      )}
    </li>
  );
}
