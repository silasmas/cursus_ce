import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../../Components/Layout/AppLayout';
import UserAvatar from '../../Components/UI/UserAvatar';
import AssessmentBadges from '../../Components/Dashboard/AssessmentBadges';
import CursusSidebar from '../../Components/Dashboard/CursusSidebar';
import FormationJourney from '../../Components/Dashboard/FormationJourney';
/**
 * Carte mentor assigné au fidèle Métamorpho.
 *
 * @param {Object} props
 * @param {Object} props.mentor Données du mentor
 * @returns {JSX.Element}
 */
function AssignedMentorCard({ mentor }) {
  if (!mentor) {
    return null;
  }

  return (
    <section className="card border-phila-orange/30 bg-gradient-to-br from-phila-orange-pale/60 to-white">
      <div className="flex items-start gap-4">
        {mentor.avatar_url ? (
          <img src={mentor.avatar_url} alt="" className="h-14 w-14 rounded-full object-cover ring-2 ring-phila-orange/30" />
        ) : (
          <span className="flex h-14 w-14 items-center justify-center rounded-full bg-phila-orange text-lg font-bold text-white">
            {mentor.name?.charAt(0)}
          </span>
        )}
        <div className="min-w-0 flex-1">
          <p className="text-[10px] font-semibold uppercase tracking-wider text-phila-orange">Votre mentor Métamorpho</p>
          <p className="font-display font-bold text-phila-black">{mentor.name}</p>
          {mentor.bio && <p className="mt-1 line-clamp-2 text-xs text-phila-gray-600">{mentor.bio}</p>}
          <Link href="/mon-espace/mentor" className="btn btn-accent mt-3 px-4 py-2 text-xs">
            Voir le profil et écrire →
          </Link>
        </div>
      </div>
    </section>
  );
}

/**
 * Bandeau ECAP : questions vacation et espace acteurs.
 *
 * @returns {JSX.Element|null}
 */
function EcapAccessBanner() {
  const { auth } = usePage().props;
  const user = auth?.user;

  if (!user?.hasEcapSession && !user?.isEcapStaff) {
    return null;
  }

  const pending = user.ecapStaffPendingQuestions ?? 0;

  return (
    <section className="mb-6 overflow-hidden rounded-2xl border-2 border-phila-orange/40 bg-gradient-to-br from-phila-orange-pale to-white">
      <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-widest text-phila-orange">ECAP · Vacation</p>
          <p className="mt-1 font-display text-lg font-bold text-phila-black">
            {user.isEcapStaff ? 'Vous êtes acteur de vacation' : 'Contactez vos acteurs de vacation'}
          </p>
          <p className="mt-1 text-sm text-phila-gray-600">
            {user.isEcapStaff
              ? 'Répondez aux questions des fidèles qui vous sont adressées.'
              : 'Posez une question à l\'enseignant, au superviseur ou au modérateur.'}
          </p>
        </div>
        <div className="flex shrink-0 flex-wrap gap-2">
          {user.hasEcapSession && (
            <Link href="/mon-espace/ecap/questions" className="btn btn-accent px-5 py-2.5 text-sm">
              Mes questions ECAP
            </Link>
          )}
          {user.hasEcapSession && (
            <>
              <Link href="/mon-espace/ecap/meditation" className="btn btn-outline px-5 py-2.5 text-sm">
                Cahiers méditation
              </Link>
            </>
          )}
          {user.isEcapStaff && (
            <Link href="/ecap/acteurs/questions" className="btn btn-outline px-5 py-2.5 text-sm">
              Espace acteurs
              {pending > 0 ? ` (${pending})` : ''}
            </Link>
          )}
        </div>
      </div>
    </section>
  );
}

/**
 * Bandeau d'accès rapide pour les comptes mentor.
 *
 * @returns {JSX.Element|null}
 */
function MentorAccessBanner() {
  const { auth } = usePage().props;

  if (!auth?.user?.isMentor) {
    return null;
  }

  const pending = auth.user.mentorPendingSubmissions ?? 0;

  return (
    <section className="mb-6 overflow-hidden rounded-2xl border-2 border-phila-black bg-phila-black text-white">
      <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-widest text-phila-orange">Compte mentor actif</p>
          <p className="mt-1 font-display text-lg font-bold">Vous avez un espace mentor séparé</p>
          <p className="mt-1 text-sm text-white/70">Corrigez les TP, consultez vos mentorés et transmettez vos avis à l&apos;administration.</p>
        </div>
        <div className="flex shrink-0 flex-wrap gap-2">
          <Link href="/mentor" className="btn btn-accent px-5 py-2.5 text-sm">
            Ouvrir l&apos;espace mentor
          </Link>
          {pending > 0 && (
            <Link href="/mentor/soumissions" className="btn btn-outline border-white/30 bg-white/10 px-5 py-2.5 text-sm text-white hover:border-phila-orange">
              {pending} TP à corriger
            </Link>
          )}
        </div>
      </div>
    </section>
  );
}

/**
 * Tableau de bord personnel du fidèle (Mon Espace) — 5 cursus PHILA-CE.
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function Dashboard({
  user,
  stats,
  cursusModules,
  activeCursusSlug,
  activeCursus,
  certificates,
  assignedMentor,
}) {
  const { flash } = usePage().props;

  const completedCursus = cursusModules.filter((m) => m.status === 'completed').length;

  return (
    <AppLayout user={user}>
      <Head title="Mon Espace" />

      <section className="hero-gradient relative overflow-hidden text-white">
        <div className="absolute -right-16 top-0 h-64 w-64 rounded-full bg-phila-orange/20 blur-3xl" />
        <div className="container-phila relative py-12 sm:py-16">
          <p className="text-xs uppercase tracking-[0.2em] text-phila-orange">Mon espace</p>
          <h1 className="mt-2 font-display text-3xl font-extrabold sm:text-4xl">
            Shalom, <span className="text-phila-orange">{user.displayName}</span>
          </h1>
          <p className="mt-2 max-w-lg text-white/75">
            Vos cursus disponibles s&apos;affichent selon votre inscription et les validations de l&apos;administration.
          </p>

          <div className="mt-8 flex flex-wrap gap-4">
            {[
              { label: 'Cursus', value: `${completedCursus}/${stats.cursus}` },
              { label: 'Progression globale', value: `${stats.progress}%` },
              { label: 'Étapes', value: `${stats.completed}/${stats.steps}` },
              { label: 'Certificats', value: stats.certificates },
            ].map((kpi) => (
              <div
                key={kpi.label}
                className="min-w-[110px] rounded-2xl border border-white/20 bg-white/10 px-5 py-4 text-center backdrop-blur-sm"
              >
                <strong className="block font-display text-2xl font-extrabold text-phila-orange">{kpi.value}</strong>
                <span className="text-xs text-white/75">{kpi.label}</span>
              </div>
            ))}
          </div>
        </div>
      </section>

      <div className="container-phila py-10">
        {flash?.status && (
          <div className="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {flash.status}
          </div>
        )}
        {flash?.error && (
          <div className="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {flash.error}
          </div>
        )}
        {flash?.info && (
          <div className="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            {flash.info}
          </div>
        )}

        <EcapAccessBanner />
        <MentorAccessBanner />

        <div className="mb-6 grid gap-3 sm:grid-cols-5">
          {cursusModules.map((module) => (
            <div
              key={module.slug}
              className={`rounded-xl border px-3 py-2 text-center text-xs ${
                module.slug === activeCursusSlug
                  ? 'border-phila-orange bg-phila-orange-pale font-semibold text-phila-orange'
                  : module.status === 'locked'
                    ? 'border-phila-gray-100 bg-phila-gray-50 text-phila-gray-400'
                    : module.status === 'presentiel_readonly'
                      ? 'border-amber-200 bg-amber-50 text-amber-800'
                      : 'border-phila-gray-100 text-phila-gray-600'
              }`}
            >
              <span className="block font-bold">{module.order}</span>
              {module.short_name}
              {(module.has_quiz || module.has_tp) && (
                <div className="mt-1 flex justify-center gap-1">
                  {module.has_quiz && <span title="Contient des tests">📝</span>}
                  {module.has_tp && <span title="Contient des TP">📋</span>}
                </div>
              )}
            </div>
          ))}
        </div>

        <div className="grid gap-6 lg:grid-cols-[300px_1fr]">
          <aside className="space-y-4">
            <Link href="/mon-espace/profil" className="card block bg-phila-black text-white transition hover:ring-2 hover:ring-phila-orange/40">
              <div className="flex items-center gap-4">
                <UserAvatar
                  avatarUrl={user.avatar_url}
                  name={user.name}
                  sizeClass="h-14 w-14"
                  textClass="text-lg font-display"
                  className="bg-phila-orange text-white"
                />
                <div>
                  <p className="font-display font-bold">{user.name}</p>
                  <p className="text-xs text-white/70">{user.email}</p>
                  <p className="mt-1 text-[10px] font-semibold text-phila-orange">Voir mon profil</p>
                </div>
              </div>
            </Link>

            <CursusSidebar modules={cursusModules} activeSlug={activeCursusSlug} />

            {assignedMentor && <AssignedMentorCard mentor={assignedMentor} />}

            {stats.locked > 0 && (
              <div className="rounded-2xl border border-phila-orange/20 bg-phila-orange-pale p-4 text-sm text-phila-gray-600">
                <strong className="text-phila-orange">{stats.locked} étape{stats.locked > 1 ? 's' : ''} verrouillée{stats.locked > 1 ? 's' : ''}</strong>
                {' '}— avancez dans le cursus en cours pour débloquer la suite.
              </div>
            )}
          </aside>

          <div className="space-y-6">
            <FormationJourney cursus={activeCursus} />

            <section className="card">
              <h2 className="font-display text-lg font-bold">Mes certificats & brevets</h2>
              {certificates.length === 0 ? (
                <p className="mt-4 text-sm text-phila-gray-600">
                  Vos brevets apparaîtront ici à la validation de chaque cursus et du parcours global.
                </p>
              ) : (
                <div className="mt-5 grid gap-4 sm:grid-cols-2">
                  {certificates.map((cert) => (
                    <div key={cert.id} className="rounded-xl border border-phila-gray-100 p-4">
                      <p className="font-medium">{cert.title}</p>
                      {cert.issued_at && (
                        <p className="mt-1 text-xs text-phila-gray-600">Délivré le {cert.issued_at}</p>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </section>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
