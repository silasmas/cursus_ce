import LoadingButton from './LoadingButton';
import DateTimePicker from './DateTimePicker';
import { localDateTimeToIso } from '../../utils/appointmentTime';

/**
 * Formulaire de programmation de rendez-vous mentor / mentoré(s).
 *
 * @param {Object} props
 * @param {Array} [props.mentees=[]] Mentorés sélectionnables
 * @param {number|null} [props.fixedAssignmentId=null] Assignation fixe (fiche mentoré)
 * @param {Array<{value: string, label: string}>} [props.channelOptions=[]] Canaux autorisés
 * @param {Function} props.onSubmit Callback soumission
 * @param {Object} props.form Formulaire Inertia useForm
 * @returns {JSX.Element}
 */
export default function ScheduleAppointmentForm({
  mentees = [],
  fixedAssignmentId = null,
  channelOptions = [],
  onSubmit,
  form,
}) {
  const channels = channelOptions.length > 0
    ? channelOptions
    : [
      { value: 'whatsapp', label: 'WhatsApp' },
      { value: 'zoom', label: 'Zoom' },
      { value: 'google_meet', label: 'Google Meet' },
    ];

  const toggleMentee = (id) => {
    const current = form.data.assignment_ids ?? [];
    form.setData(
      'assignment_ids',
      current.includes(id) ? current.filter((x) => x !== id) : [...current, id],
    );
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    form.transform((data) => ({
      ...data,
      scheduled_at: localDateTimeToIso(data.scheduled_at),
    }));
    onSubmit(event);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {!fixedAssignmentId && mentees.length > 0 && (
        <div>
          <p className="label-field">Mentoré(s)</p>
          <div className="flex flex-wrap gap-2">
            {mentees.map((m) => (
              <label
                key={m.assignment_id}
                className={`cursor-pointer rounded-full border px-3 py-1.5 text-xs font-medium ${
                  form.data.assignment_ids?.includes(m.assignment_id)
                    ? 'border-phila-orange bg-phila-orange-pale text-phila-orange'
                    : 'border-phila-gray-100'
                }`}
              >
                <input
                  type="checkbox"
                  className="sr-only"
                  checked={form.data.assignment_ids?.includes(m.assignment_id)}
                  onChange={() => toggleMentee(m.assignment_id)}
                />
                {m.name}
              </label>
            ))}
          </div>
        </div>
      )}

      <div className="grid gap-4 sm:grid-cols-2">
        <DateTimePicker
          value={form.data.scheduled_at}
          onChange={(value) => form.setData('scheduled_at', value)}
          required
        />
        <div>
          <label className="label-field">Canal</label>
          <select
            className="input-field"
            value={form.data.channel}
            onChange={(e) => form.setData('channel', e.target.value)}
            required
          >
            {channels.map((c) => (
              <option key={c.value} value={c.value}>{c.label}</option>
            ))}
          </select>
        </div>
      </div>

      <div>
        <label className="label-field">Lien de la réunion (Zoom / Meet / WhatsApp)</label>
        <input
          type="url"
          className="input-field"
          placeholder="https://…"
          value={form.data.meeting_url}
          onChange={(e) => form.setData('meeting_url', e.target.value)}
        />
      </div>

      <div>
        <label className="label-field">Notes</label>
        <textarea
          className="input-field min-h-[80px]"
          value={form.data.notes}
          onChange={(e) => form.setData('notes', e.target.value)}
          placeholder="Objet du rendez-vous, consignes…"
        />
      </div>

      <LoadingButton
        type="submit"
        processing={form.processing}
        loadingText="Programmation…"
        className="btn btn-accent text-sm"
      >
        Programmer le rendez-vous
      </LoadingButton>
    </form>
  );
}
