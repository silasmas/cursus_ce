import { Head, Link, usePage } from '@inertiajs/react';
import MentorLayout from '../../Components/Layout/MentorLayout';
import AppointmentCard from '../../Components/UI/AppointmentCard';

/**
 * Carte statistique du tableau de bord mentor.
 */
function StatCard({ label, value, hint, href }) {
  const content = (
    <div className="card h-full">
      <p className="text-[10px] font-semibold uppercase tracking-wider text-phila-gray-500">{label}</p>
      <p className="mt-2 font-display text-3xl font-bold text-phila-orange">{value}</p>
      {hint && <p className="mt-1 text-xs text-phila-gray-600">{hint}</p>}
    </div>
  );

  if (href) {
    return <Link href={href} className="block transition hover:opacity-90">{content}</Link>;
  }

  return content;
}

/**
 * Tableau de bord mentor — statistiques et activité récente.
 */
export default function Dashboard({ mentor, stats, pendingSubmissions, recentAppointments = [] }) {
  const { auth } = usePage().props;

  return (
    <MentorLayout active="dashboard">
      <Head title="Espace Mentor" />
      <section className="hero-gradient text-white">
        <div className="container-phila py-12">
          <p className="text-xs uppercase tracking-widest text-phila-orange">Espace mentor</p>
          <h1 className="mt-2 font-display text-3xl font-bold">Bonjour, {mentor.name}</h1>
          <p className="mt-2 max-w-lg text-white/75">
            Vue d&apos;ensemble de vos mentorés sur les {stats.period_days} derniers jours.
          </p>
        </div>
      </section>

      <div className="container-phila py-10">
        {(pendingSubmissions > 0 || auth?.user?.mentorPendingSubmissions > 0) && (
          <Link
            href="/mentor/soumissions"
            className="mb-6 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 transition hover:border-amber-300"
          >
            <div>
              <p className="font-semibold text-amber-900">Soumissions en attente de correction</p>
              <p className="text-sm text-amber-800">Des mentorés attendent votre aval pour progresser.</p>
            </div>
            <span className="flex h-10 w-10 items-center justify-center rounded-full bg-amber-200 font-bold text-amber-900">
              {pendingSubmissions ?? auth?.user?.mentorPendingSubmissions}
            </span>
          </Link>
        )}

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard label="Mentorés actifs" value={stats.total_mentees} hint={`+${stats.new_mentees_period} sur la période`} href="/mentor/mentores" />
          <StatCard label="TP à corriger" value={stats.pending_corrections} href="/mentor/soumissions" />
          <StatCard label="RDV à venir" value={stats.upcoming_appointments} href="/mentor/formulaires" />
          <StatCard label="En attente de réponse" value={stats.appointments_awaiting_response} href="/mentor/mentores" />
        </div>

        <div className="mt-8 grid gap-6 lg:grid-cols-2">
          <div className="card">
            <h2 className="font-display text-lg font-bold">Progression des mentorés</h2>
            <ul className="mt-4 space-y-3 text-sm">
              <li className="flex justify-between rounded-xl bg-green-50 px-4 py-3">
                <span>Parcours terminé</span>
                <strong className="text-green-800">{stats.by_progress.finished}</strong>
              </li>
              <li className="flex justify-between rounded-xl bg-blue-50 px-4 py-3">
                <span>En cours</span>
                <strong className="text-blue-800">{stats.by_progress.in_progress}</strong>
              </li>
              <li className="flex justify-between rounded-xl bg-amber-50 px-4 py-3">
                <span>En attente de validation TP</span>
                <strong className="text-amber-800">{stats.by_progress.pending_validation}</strong>
              </li>
            </ul>
          </div>

          <div className="card">
            <h2 className="font-display text-lg font-bold">Répartition par âge</h2>
            <ul className="mt-4 space-y-2 text-sm">
              <li className="flex justify-between"><span>25 ans et moins</span><strong>{stats.by_age['-25']}</strong></li>
              <li className="flex justify-between"><span>26 – 35 ans</span><strong>{stats.by_age['26-35']}</strong></li>
              <li className="flex justify-between"><span>36 – 45 ans</span><strong>{stats.by_age['36-45']}</strong></li>
              <li className="flex justify-between"><span>46 ans et plus</span><strong>{stats.by_age['46+']}</strong></li>
              {stats.by_age.inconnu > 0 && (
                <li className="flex justify-between text-phila-gray-500"><span>Non renseigné</span><strong>{stats.by_age.inconnu}</strong></li>
              )}
            </ul>
          </div>
        </div>

        {recentAppointments.length > 0 && (
          <div className="card mt-8 space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-lg font-bold">Prochains rendez-vous</h2>
              <Link href="/mentor/formulaires" className="text-sm text-phila-orange hover:underline">Tout voir →</Link>
            </div>
            <ul className="space-y-3">
              {recentAppointments.map((appt) => (
                <AppointmentCard key={appt.id} appointment={appt} canEdit={appt.can_edit} />
              ))}
            </ul>
          </div>
        )}

        <div className="mt-8 flex flex-wrap gap-3">
          <Link href="/mentor/mentores" className="btn btn-accent text-sm">Mes mentorés</Link>
          <Link href="/mentor/formulaires#rdv" className="btn btn-outline text-sm">RDV & TP (formulaires)</Link>
          <Link href="/mentor/formulaires#cloture" className="btn btn-outline text-sm">Clôturer un accompagnement</Link>
          <Link href="/mentor/soumissions" className="btn btn-outline text-sm">Corriger les TP</Link>
        </div>
      </div>
    </MentorLayout>
  );
}
