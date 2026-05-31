import { Link } from '@inertiajs/react';

/**
 * Détermine l'action à afficher pour une étape (robuste même sans is_focus backend).
 *
 * @param {Object} step Données de l'étape
 * @returns {{ label: string, variant: string }|null}
 */
export function resolveStepAction(step, readOnlyOnline = false) {
  if (step.status === 'locked') {
    return null;
  }

  if (readOnlyOnline || step.module_closed_review) {
    if (step.status === 'completed' || step.is_reviewable) {
      return { label: 'Revoir le cours', variant: 'outline' };
    }

    return null;
  }

  const reviewable = step.is_reviewable === true || step.status === 'completed';
  const focus = step.is_focus === true
    || (step.is_focus === undefined && ['in_progress', 'available'].includes(step.status));

  if (reviewable && !focus) {
    return { label: 'Revoir le cours', variant: 'outline' };
  }

  if (step.status === 'in_progress' || (focus && step.has_started)) {
    return { label: 'Reprendre', variant: 'primary' };
  }

  if (step.status === 'available' || focus) {
    return {
      label: step.has_started ? 'Reprendre' : 'Commencer',
      variant: 'accent',
    };
  }

  if (reviewable) {
    return { label: 'Revoir le cours', variant: 'outline' };
  }

  return { label: 'Accéder au cours', variant: 'accent' };
}

const variantClass = {
  outline: 'btn btn-outline',
  primary: 'btn btn-primary',
  accent: 'btn btn-accent',
};

/**
 * Bouton d'action pour une étape du parcours.
 *
 * @param {Object} props
 * @param {Object} props.step Étape du parcours
 * @returns {JSX.Element|null}
 */
export default function StepActionButton({ step, readOnlyOnline = false }) {
  const action = resolveStepAction(step, readOnlyOnline);

  if (!action) {
    return null;
  }

  return (
    <Link
      href={`/mon-espace/cours/${step.id}`}
      className={`${variantClass[action.variant]} w-full px-4 py-2 text-xs sm:w-auto`}
    >
      {action.label}
    </Link>
  );
}
