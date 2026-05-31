import { Link } from '@inertiajs/react';
import ModuleCountdownBadge from '../UI/ModuleCountdownBadge';
import AssessmentBadges from './AssessmentBadges';
import InstructorChip from './InstructorChip';
import StepActionButton from './StepActionButton';

/**
 * Regroupe les étapes plates en modules si content_modules est absent.
 *
 * @param {Object} cursus Données du cursus
 * @returns {Array}
 */
function resolveModules(cursus) {
  if (Array.isArray(cursus.content_modules) && cursus.content_modules.length > 0) {
    return cursus.content_modules;
  }

  if (!Array.isArray(cursus.steps) || cursus.steps.length === 0) {
    return [];
  }

  const grouped = {};

  for (const step of cursus.steps) {
    const name = step.module || 'Module général';
    const key = step.course_module_id ?? name;

    if (!grouped[key]) {
      grouped[key] = {
        name,
        course_module_id: step.course_module_id ?? null,
        steps: [],
        completed: 0,
        total: 0,
        has_quiz: false,
        has_tp: false,
        quiz_count: 0,
        tp_count: 0,
      };
    }

    grouped[key].steps.push(step);
    grouped[key].total += 1;

    if (step.status === 'completed') {
      grouped[key].completed += 1;
    }

    if (step.has_quiz) {
      grouped[key].has_quiz = true;
      grouped[key].quiz_count += step.quiz_count ?? 1;
    }

    if (step.has_tp) {
      grouped[key].has_tp = true;
      grouped[key].tp_count += step.tp_count ?? 1;
    }
  }

  return Object.values(grouped).map((module) => ({
    ...module,
    progress: module.total > 0 ? Math.round((module.completed / module.total) * 100) : 0,
  }));
}

/**
 * Affiche les étapes du cursus actif avec déblocage progressif.
 *
 * @param {Object} props
 * @param {Object|null} props.cursus Cursus actif
 * @returns {JSX.Element}
 */
export default function FormationJourney({ cursus }) {
  if (!cursus) {
    return (
      <section className="card">
        <h2 className="font-display text-lg font-bold">Mon parcours</h2>
        <p className="mt-4 text-sm text-phila-gray-600">Sélectionnez un cursus pour voir vos étapes.</p>
      </section>
    );
  }

  if (cursus.status === 'locked') {
    return (
      <section className="card text-center">
        <h2 className="font-display text-xl font-bold">{cursus.name}</h2>
        <p className="mt-4 rounded-xl bg-phila-gray-50 px-4 py-3 text-sm text-phila-gray-600">
          Terminez le cursus précédent pour débloquer celui-ci.
        </p>
      </section>
    );
  }

  const readOnlyOnline = cursus.status === 'presentiel_readonly';

  if (readOnlyOnline) {
    return (
      <section className="card">
        <div className="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          <strong>Présentiel — accès en ligne désactivé.</strong>
          {' '}Vous pouvez consulter le contenu ECAP en lecture seule. La progression, les quiz et les TP en ligne ne sont pas disponibles.
        </div>
        {renderJourneyContent(cursus, readOnlyOnline)}
      </section>
    );
  }

  return renderJourneyContent(cursus, false);
}

/**
 * Message affiché lorsqu'un module n'a pas encore d'étapes visibles.
 *
 * @param {Object} module Module du parcours
 * @returns {string}
 */
function moduleEmptyMessage(module) {
  if (module.schedule_is_open === false && module.schedule_starts_on) {
    return `Ce module sera accessible à partir du ${module.schedule_starts_on} (calendrier ECAP). Les leçons (vidéos et textes) apparaîtront ici à cette date.`;
  }

  if (module.schedule_starts_on) {
    return `Aucune leçon publiée pour ce module pour le moment. Vérifiez que les chapitres sont bien publiés dans l'administration (Contenu pédagogique → Chapitres).`;
  }

  return 'Les chapitres de ce module seront publiés prochainement selon le calendrier de session.';
}

/**
 * Contenu principal du parcours (modules et étapes).
 */
function renderJourneyContent(cursus, readOnlyOnline) {
  if (cursus.status === 'locked') {
    return null;
  }

  const modules = resolveModules(cursus);

  if (modules.length === 0) {
    return (
      <section className="card">
        <h2 className="font-display text-xl font-bold">{cursus.name}</h2>
        <p className="mt-4 rounded-xl bg-phila-orange-pale px-4 py-3 text-sm text-phila-gray-600">
          Les étapes de ce cursus seront bientôt publiées.
        </p>
      </section>
    );
  }

  return (
    <section className="card">
      <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.15em] text-phila-orange">
            Cursus {cursus.order} — {cursus.subtitle}
          </p>
          <h2 className="font-display text-xl font-bold">{cursus.name}</h2>
          <p className="mt-1 max-w-xl text-sm text-phila-gray-600">{cursus.objective}</p>
        </div>
        <div className="rounded-2xl bg-phila-orange-pale px-5 py-3 text-center">
          <strong className="block font-display text-2xl font-extrabold text-phila-orange">{cursus.progress ?? 0}%</strong>
          <span className="text-xs text-phila-gray-600">{cursus.stats?.completed ?? 0}/{cursus.stats?.total ?? 0} étapes</span>
        </div>
      </div>

      <div className="prog-bar mb-8">
        <div className="prog-bar-fill" style={{ width: `${cursus.progress ?? 0}%` }} />
      </div>

      <div className="space-y-8">
        {modules.map((module) => (
          <div key={module.course_module_id ?? module.name}>
            <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
              <div className="min-w-0 flex-1">
                <h3 className="font-display font-bold text-phila-black">{module.name}</h3>
                {module.schedule_starts_on && (
                  <p className="mt-1 text-xs text-phila-gray-500">
                    Calendrier : {module.schedule_starts_on}
                    {module.schedule_ends_on && module.schedule_ends_on !== module.schedule_starts_on
                      ? ` → ${module.schedule_ends_on}`
                      : ''}
                  </p>
                )}
                <AssessmentBadges item={module} muted={false} />
                {module.countdown && <ModuleCountdownBadge countdown={module.countdown} className="mt-3" />}
              </div>
              <span className="text-xs font-medium text-phila-gray-600">
                {module.completed}/{module.total} · {module.progress}%
              </span>
            </div>

            <div className="space-y-3">
              {module.steps.length === 0 && (
                <p className="rounded-xl border border-dashed border-phila-gray-200 bg-phila-gray-50 px-4 py-3 text-sm text-phila-gray-600">
                  {moduleEmptyMessage(module)}
                </p>
              )}
              {module.steps.map((step) => (
                <StepCard key={step.id} step={step} readOnlyOnline={readOnlyOnline} />
              ))}

              {module.module_exit_quiz && (
                <ModuleExitQuizCard quiz={module.module_exit_quiz} readOnlyOnline={readOnlyOnline} />
              )}
            </div>
          </div>
        ))}
      </div>
    </section>
  );
}

/**
 * Carte d'une étape du parcours avec action de navigation.
 *
 * @param {Object} props
 * @param {Object} props.step Données de l'étape
 * @returns {JSX.Element}
 */
function StepCard({ step, readOnlyOnline = false }) {
  const effectiveStep = readOnlyOnline && step.status === 'locked'
    ? { ...step, status: 'available' }
    : step;
  const config = statusConfig[effectiveStep.status] ?? statusConfig.available;
  const hasPending = effectiveStep.pending_labels?.length > 0 && effectiveStep.status !== 'completed';

  return (
    <div className={`flex flex-col gap-4 rounded-xl border p-4 sm:flex-row sm:items-start ${config.container}`}>
      <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full ${config.icon}`}>
        {config.iconContent(step.order)}
      </div>

      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-2">
          <p className={`font-medium ${step.status === 'locked' ? 'text-phila-gray-400' : 'text-phila-black'}`}>
            {step.title}
          </p>
          <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide ${config.badge}`}>
            {config.label}
          </span>
        </div>
        <p className="mt-0.5 text-xs text-phila-gray-600">
          Étape {step.order}
          {step.course && ` · ${step.course}`}
          {step.completed_at && ` · Terminée le ${step.completed_at}`}
        </p>

        <InstructorChip instructor={step.instructor} />
        <AssessmentBadges item={step} muted={step.status === 'locked'} />

        {step.lock_reason === 'module_closed' && (
          <p className="mt-2 text-xs text-phila-gray-500">
            Période du module terminée — chapitre non achevé.
          </p>
        )}

        {hasPending && step.lock_reason !== 'module_closed' && (
          <ul className="mt-2 space-y-0.5 text-xs text-amber-700">
            {step.pending_labels.map((label) => (
              <li key={label}>→ {label}</li>
            ))}
          </ul>
        )}
      </div>

      <div className="flex shrink-0 flex-wrap gap-2 sm:flex-col sm:items-stretch">
        <StepActionButton
          step={effectiveStep}
          readOnlyOnline={readOnlyOnline || step.module_closed_review === true}
        />
        {step.status === 'locked' && (
          <div className="flex h-11 w-11 items-center justify-center text-phila-gray-400">
            <LockIcon />
          </div>
        )}
      </div>
    </div>
  );
}

/**
 * Carte du quiz obligatoire de fin de module ECAP (M5).
 *
 * @param {Object} props
 * @param {Object} props.quiz Données du quiz M5
 * @param {boolean} props.readOnlyOnline Mode présentiel lecture seule
 * @returns {JSX.Element}
 */
function ModuleExitQuizCard({ quiz, readOnlyOnline = false }) {
  const passed = quiz.passed === true;
  const canAttempt = quiz.can_attempt === true && !readOnlyOnline;
  const waitingChapters = !passed && !canAttempt && !readOnlyOnline;

  return (
    <div className={`rounded-xl border p-4 ${passed ? 'border-green-200 bg-green-50/60' : 'border-phila-orange/40 bg-phila-orange-pale/30'}`}>
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-phila-orange">Quiz fin de module</p>
          <h4 className="font-display font-bold text-phila-black">{quiz.title}</h4>
          <p className="mt-1 text-xs text-phila-gray-600">
            {quiz.required_questions} questions · Seuil {quiz.passing_score}%
            {!quiz.is_configured && ' · En cours de configuration par l\'administration'}
          </p>
        </div>

        {passed ? (
          <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Réussi</span>
        ) : canAttempt ? (
          <Link href={`/mon-espace/tests/${quiz.assessment_id}`} className="btn btn-accent px-4 py-2 text-sm">
            Passer le quiz
          </Link>
        ) : waitingChapters ? (
          <span className="text-xs text-phila-gray-600">Terminez d&apos;abord tous les chapitres du module</span>
        ) : (
          <span className="text-xs text-phila-gray-600">Consultation seule</span>
        )}
      </div>
    </div>
  );
}

const statusConfig = {
  completed: {
    label: 'Terminée',
    container: 'border-green-200 bg-green-50/50',
    icon: 'bg-green-100 text-green-700',
    badge: 'bg-green-100 text-green-700',
    iconContent: () => (
      <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
      </svg>
    ),
  },
  in_progress: {
    label: 'En cours',
    container: 'border-phila-orange/30 bg-phila-orange-pale/40',
    icon: 'bg-phila-orange text-white',
    badge: 'bg-phila-orange-pale text-phila-orange',
    iconContent: (order) => <span className="text-sm font-bold">{order}</span>,
  },
  available: {
    label: 'Disponible',
    container: 'border-phila-orange/40 bg-white shadow-sm',
    icon: 'bg-phila-orange text-white ring-2 ring-phila-orange/20',
    badge: 'bg-phila-orange-pale text-phila-orange',
    iconContent: (order) => <span className="text-sm font-bold">{order}</span>,
  },
  locked: {
    label: 'Verrouillée',
    container: 'border-phila-gray-100 bg-phila-gray-50/80 opacity-75',
    icon: 'bg-phila-gray-100 text-phila-gray-400',
    badge: 'bg-phila-gray-100 text-phila-gray-400',
    iconContent: () => <LockIcon />,
  },
};

function LockIcon() {
  return (
    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
      <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
  );
}
