const Shepherd = window.Shepherd;

const FRENCH_LABELS = {
  cancel: 'Fermer',
  alertError: 'Impossible de démarrer la visite guidée. Réessayez.',
};

/**
 * Attache data-tour aux entrées du menu latéral.
 *
 * @param {Record<string, string>} navigationMap id → libellé menu
 * @returns {void}
 */
function applyNavigationTourAttributes(navigationMap) {
  const sidebar = document.querySelector('.fi-sidebar');

  if (!sidebar) {
    return;
  }

  sidebar.querySelectorAll('.fi-sidebar-group').forEach((group) => {
    const button = group.querySelector('.fi-sidebar-group-button');

    if (button && button.getAttribute('aria-expanded') === 'false') {
      button.click();
    }
  });

  Object.entries(navigationMap).forEach(([tourId, navText]) => {
    const candidates = sidebar.querySelectorAll('.fi-sidebar-item, .fi-sidebar-group-item, [role="menuitem"]');

    candidates.forEach((item) => {
      const label = item.textContent?.replace(/\s+/g, ' ').trim() ?? '';
      const link = item.querySelector('a') ?? item;

      if (label === navText || label.includes(navText)) {
        link.setAttribute('data-tour', tourId);
      }
    });
  });
}

/**
 * Construit et démarre la visite Shepherd.
 *
 * @returns {InstanceType<typeof Shepherd.Tour>}
 */
export function initializeShepherdTour() {
  const tour = new Shepherd.Tour({
    useModalOverlay: true,
    defaultStepOptions: {
      classes: 'shepherd-theme-phila',
      scrollTo: { behavior: 'smooth', block: 'nearest', inline: 'nearest' },
      cancelIcon: {
        enabled: true,
        label: FRENCH_LABELS.cancel,
      },
      modalOverlayOpeningRadius: 8,
      modalOverlayOpeningPadding: 6,
    },
    tourName: 'phila-admin-tour',
  });

  let originalActiveItems = [];

  tour.on('start', () => {
    applyNavigationTourAttributes(window.navigationMap ?? {});
    originalActiveItems = Array.from(
      document.querySelectorAll('.fi-sidebar-item.fi-active, .fi-sidebar-group-item.fi-active, [data-tour].fi-active'),
    );

    originalActiveItems.forEach((item) => {
      item.classList.add('tour-original-active');
      item.classList.remove('fi-active');
    });
  });

  tour.on('show', (event) => {
    if (!event.step) {
      return;
    }

    const stepId = event.step.id;
    localStorage.setItem('shepherd-tour-current-step', stepId);
    localStorage.setItem('shepherd-tour-in-progress', 'true');

    document.querySelectorAll('.shepherd-tour-active-nav').forEach((item) => {
      item.classList.remove('shepherd-tour-active-nav');
    });

    window.setTimeout(() => {
      const navItem = document.querySelector(`[data-tour="${stepId}"]`);

      if (!navItem) {
        return;
      }

      const navContainer =
        navItem.closest('.fi-sidebar-item') ??
        navItem.closest('.fi-sidebar-group-item') ??
        navItem.closest('li') ??
        navItem;

      navContainer.classList.add('shepherd-tour-active-nav');
      navContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
    }, 200);
  });

  const restoreNav = () => {
    localStorage.removeItem('shepherd-tour-current-step');
    localStorage.removeItem('shepherd-tour-in-progress');

    document.querySelectorAll('.shepherd-tour-active-nav').forEach((item) => {
      item.classList.remove('shepherd-tour-active-nav');
    });

    originalActiveItems.forEach((item) => {
      item.classList.remove('tour-original-active');
      item.classList.add('fi-active');
    });
  };

  tour.on('complete', () => {
    restoreNav();
    localStorage.setItem('shepherd-tour-completed', 'true');
  });

  tour.on('cancel', restoreNav);

  const welcomeStep = window.customWelcomeStep;
  const finishStep = window.customFinishStep;
  const dynamicSteps = window.dynamicTourSteps ?? [];
  const allSteps = [welcomeStep, ...dynamicSteps, finishStep].filter(Boolean);

  allSteps.forEach((stepData) => {
    const stepConfig = {
      id: stepData.id,
      title: stepData.title,
      text: stepData.text,
    };

    if (stepData.attachTo) {
      const element = document.querySelector(stepData.attachTo);

      if (element) {
        stepConfig.attachTo = {
          element,
          on: stepData.position ?? 'right',
        };
      }
    }

    const stepButtons = stepData.buttons ?? [
      { text: 'Précédent', action: 'back', secondary: true },
      { text: 'Suivant', action: 'next', secondary: false },
    ];

    stepConfig.buttons = stepButtons.map((btnData) => {
      const button = {
        text: btnData.text,
        secondary: Boolean(btnData.secondary),
      };

      if (btnData.action === 'back') {
        button.action = tour.back;
      } else if (btnData.action === 'next') {
        button.action = tour.next;
      } else if (btnData.action === 'cancel') {
        button.action = tour.cancel;
      } else if (btnData.action === 'complete') {
        button.action = tour.complete;
      }

      return button;
    });

    tour.addStep(stepConfig);
  });

  return tour;
}

document.addEventListener('DOMContentLoaded', () => {
  applyNavigationTourAttributes(window.navigationMap ?? {});

  document.addEventListener('livewire:navigated', () => {
    window.setTimeout(() => applyNavigationTourAttributes(window.navigationMap ?? {}), 250);
  });

  document.querySelectorAll('[data-shepherd-tour-trigger]').forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();

      try {
        const tour = initializeShepherdTour();
        const inProgress = localStorage.getItem('shepherd-tour-in-progress');
        const currentStepId = localStorage.getItem('shepherd-tour-current-step');

        if (inProgress === 'true' && currentStepId) {
          tour.show(currentStepId);
        } else {
          tour.start();
        }
      } catch (error) {
        console.error(error);
        window.alert(FRENCH_LABELS.alertError);
      }
    });
  });
});

export default initializeShepherdTour;
