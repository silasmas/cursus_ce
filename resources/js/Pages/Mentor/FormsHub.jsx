import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import MentorLayout from '../../Components/Layout/MentorLayout';
import LoadingButton from '../../Components/UI/LoadingButton';
import MenteeMultiSelect from '../../Components/UI/MenteeMultiSelect';
import ScheduleAppointmentForm from '../../Components/UI/ScheduleAppointmentForm';
import { localDateTimeToIso } from '../../utils/appointmentTime';

/**
 * Formulaire de remise de TP pour un ou plusieurs mentorés.
 */
function MentorTpBulkForm({ mentees, tpAssessments }) {
  const form = useForm({
    assignment_ids: [],
    assessment_id: tpAssessments[0]?.id ?? '',
    answer_text: '',
    file: null,
  });

  const selectedAssessment = useMemo(
    () => tpAssessments.find((tp) => tp.id === Number(form.data.assessment_id)),
    [tpAssessments, form.data.assessment_id],
  );

  const programId = selectedAssessment?.program_id ?? null;

  const submitTp = (event) => {
    event.preventDefault();
    if (!form.data.assignment_ids?.length) {
      return;
    }
    form.transform((data) => ({
      ...data,
      assessment_id: Number(data.assessment_id),
    }));
    form.post('/mentor/tp', {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => form.reset('answer_text', 'file'),
    });
  };

  if (tpAssessments.length === 0) {
    return (
      <p className="text-sm text-phila-gray-600">Aucun TP publié pour vos programmes mentorés.</p>
    );
  }

  return (
    <form onSubmit={submitTp} className="space-y-4">
      <div>
        <label className="label-field">Travail pratique</label>
        <select
          className="input-field"
          value={form.data.assessment_id}
          onChange={(e) => {
            form.setData({
              ...form.data,
              assessment_id: e.target.value,
              assignment_ids: [],
            });
          }}
          required
        >
          {tpAssessments.map((tp) => (
            <option key={tp.id} value={tp.id}>
              {tp.title}
              {tp.chapter ? ` — ${tp.chapter}` : ''}
              {tp.program_name ? ` [${tp.program_name}]` : ''}
            </option>
          ))}
        </select>
      </div>

      <div>
        <p className="label-field">Mentoré(s) concerné(s)</p>
        <MenteeMultiSelect
          mentees={mentees}
          selectedIds={form.data.assignment_ids}
          onChange={(ids) => form.setData('assignment_ids', ids)}
          filterProgramId={programId}
        />
      </div>

      <div>
        <label className="label-field">Réponse / contenu du TP</label>
        <textarea
          className="input-field min-h-[120px]"
          placeholder="Saisissez le travail remis…"
          value={form.data.answer_text}
          onChange={(e) => form.setData('answer_text', e.target.value)}
        />
      </div>

      <div>
        <label className="label-field">Fichier (optionnel)</label>
        <input
          type="file"
          className="input-field"
          onChange={(e) => form.setData('file', e.target.files[0])}
        />
      </div>

      <p className="text-xs text-amber-700">
        La remise sera invisible pour chaque mentoré tant que l&apos;administration ne l&apos;aura pas publiée.
      </p>

      <LoadingButton
        type="submit"
        processing={form.processing}
        loadingText="Remise en cours…"
        className="btn btn-accent text-sm"
        disabled={!form.data.assignment_ids?.length}
      >
        Remettre le TP
      </LoadingButton>
    </form>
  );
}

/**
 * Clôture d'accompagnement avec rapport à l'administration.
 */
function ClosureForm({ mentees }) {
  const form = useForm({
    assignment_ids: [],
    report_body: '',
    confirm: false,
  });

  const submit = (event) => {
    event.preventDefault();
    form.post('/mentor/accompagnement/cloturer', {
      preserveScroll: true,
      onSuccess: () => form.reset(),
    });
  };

  return (
    <form onSubmit={submit} className="space-y-4">
      <div>
        <p className="label-field">Mentoré(s) à clôturer</p>
        <MenteeMultiSelect
          mentees={mentees}
          selectedIds={form.data.assignment_ids}
          onChange={(ids) => form.setData('assignment_ids', ids)}
        />
      </div>

      <div>
        <label className="label-field">Rapport pour l&apos;administration</label>
        <textarea
          className="input-field min-h-[160px]"
          placeholder="Bilan de l'accompagnement, progression du mentoré, recommandations…"
          value={form.data.report_body}
          onChange={(e) => form.setData('report_body', e.target.value)}
          required
          minLength={20}
        />
      </div>

      <label className="flex items-start gap-2 text-sm">
        <input
          type="checkbox"
          className="mt-1"
          checked={form.data.confirm}
          onChange={(e) => form.setData('confirm', e.target.checked)}
          required
        />
        <span>
          Je confirme clôturer l&apos;accompagnement sélectionné. Le mentoré sera informé et pourra
          laisser son avis ; il ne pourra plus utiliser le chat mentor.
        </span>
      </label>

      <LoadingButton
        type="submit"
        processing={form.processing}
        loadingText="Clôture en cours…"
        className="btn btn-outline border-red-300 text-red-800 hover:bg-red-50 text-sm"
        disabled={!form.data.assignment_ids?.length}
      >
        Clôturer et envoyer le rapport
      </LoadingButton>
    </form>
  );
}

/**
 * Hub central des formulaires et démarches mentor.
 */
export default function FormsHub({
  summary,
  mentees = [],
  tpAssessments = [],
  appointmentChannelOptions = [],
}) {
  const { flash } = usePage().props;
  const defaultChannel = appointmentChannelOptions[0]?.value ?? 'zoom';

  const appointmentForm = useForm({
    assignment_ids: [],
    scheduled_at: '',
    channel: defaultChannel,
    meeting_url: '',
    notes: '',
  });

  const scheduleAppointment = (event) => {
    event.preventDefault();
    appointmentForm.transform((data) => ({
      ...data,
      scheduled_at: localDateTimeToIso(data.scheduled_at),
    }));
    appointmentForm.post('/mentor/rendez-vous', {
      preserveScroll: true,
      onSuccess: () => appointmentForm.reset('scheduled_at', 'meeting_url', 'notes'),
    });
  };

  const cards = [
    {
      title: 'Corriger les TP',
      description: 'Valider ou refuser les travaux remis par vos mentorés.',
      href: '/mentor/soumissions',
      count: summary.pending_corrections,
      icon: '📋',
    },
    {
      title: 'Réponses aux rendez-vous',
      description: 'Acceptations, refus et reports proposés par vos mentorés.',
      href: '/mentor/mentores',
      count: summary.postponed_appointments,
      icon: '💬',
    },
  ];

  return (
    <MentorLayout active="forms">
      <Head title="Formulaires & démarches" />
      <div className="container-phila py-10">
        <h1 className="font-display text-2xl font-bold">Formulaires & démarches</h1>
        <p className="mt-2 text-sm text-phila-gray-600">
          Programmez des RDV, remettez des TP et clôturez un accompagnement — sans passer par chaque fiche mentoré.
        </p>

        {flash?.status && (
          <div className="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}
        {flash?.error && (
          <div className="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {flash.error}
          </div>
        )}

        <div className="mt-8 grid gap-4 sm:grid-cols-2">
          {cards.map((card) => (
            <Link
              key={card.title}
              href={card.href}
              className="card flex gap-4 transition hover:border-phila-orange/40 hover:shadow-md"
            >
              <span className="text-3xl">{card.icon}</span>
              <div className="flex-1">
                <div className="flex items-start justify-between gap-2">
                  <h2 className="font-display font-bold">{card.title}</h2>
                  {card.count > 0 && (
                    <span className="rounded-full bg-phila-orange px-2 py-0.5 text-[10px] font-bold text-white">
                      {card.count}
                    </span>
                  )}
                </div>
                <p className="mt-1 text-sm text-phila-gray-600">{card.description}</p>
              </div>
            </Link>
          ))}
        </div>

        <div id="rdv" className="card mt-8 space-y-4">
          <h2 className="font-display text-lg font-bold">Programmer un rendez-vous</h2>
          <p className="text-sm text-phila-gray-600">
            Sélectionnez un ou plusieurs mentorés pour le même créneau (WhatsApp, Zoom ou Meet).
          </p>
          {mentees.length === 0 ? (
            <p className="text-sm text-phila-gray-600">Aucun mentoré actif pour le moment.</p>
          ) : (
            <ScheduleAppointmentForm
              mentees={mentees}
              channelOptions={appointmentChannelOptions}
              form={appointmentForm}
              onSubmit={scheduleAppointment}
            />
          )}
        </div>

        <div id="tp" className="card mt-8 space-y-4">
          <h2 className="font-display text-lg font-bold">Remettre un TP</h2>
          <p className="text-sm text-phila-gray-600">
            Un même TP peut être remis pour plusieurs mentorés du même programme.
          </p>
          <MentorTpBulkForm mentees={mentees} tpAssessments={tpAssessments} />
        </div>

        <div id="cloture" className="card mt-8 space-y-4">
          <h2 className="font-display text-lg font-bold">Clôturer un accompagnement</h2>
          <p className="text-sm text-phila-gray-600">
            Transmettez votre rapport à l&apos;administration. Le mentoré verra que l&apos;accompagnement est
            clôturé et pourra donner son avis.
          </p>
          <ClosureForm mentees={mentees} />
        </div>

        <div className="mt-8 flex flex-wrap gap-3">
          <Link href="/mentor/mentores" className="btn btn-outline text-sm">
            Fiches mentorés (chat, détail)
          </Link>
          <Link href="/mentor/soumissions" className="btn btn-outline text-sm">
            Corrections TP
          </Link>
        </div>
      </div>
    </MentorLayout>
  );
}
