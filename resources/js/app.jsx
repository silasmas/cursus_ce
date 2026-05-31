import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import PageLoader from './Components/Skeletons/PageLoader';

/**
 * Enveloppe Inertia avec skeleton de chargement entre les pages.
 *
 * @param {Object} props
 * @param {React.ComponentType} props.App Composant Inertia
 * @param {Object} props.props Props de la page
 * @returns {JSX.Element}
 */
function InertiaApp({ App, props }) {
  const [isNavigating, setIsNavigating] = useState(false);
  const [pendingUrl, setPendingUrl] = useState('/');

  useEffect(() => {
    const removeStart = router.on('start', (event) => {
      const visit = event.detail.visit;

      if (visit.method !== 'get' || visit.prefetch) {
        return;
      }

      setPendingUrl(visit.url.pathname || '/');
      setIsNavigating(true);
    });

    const removeFinish = router.on('finish', () => {
      setIsNavigating(false);
    });

    return () => {
      removeStart();
      removeFinish();
    };
  }, []);

  return (
    <>
      <App {...props} />
      {isNavigating && createPortal(
        <PageLoader url={pendingUrl} />,
        document.body,
      )}
    </>
  );
}

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });
    return pages[`./Pages/${name}.jsx`];
  },
  setup({ el, App, props }) {
    createRoot(el).render(<InertiaApp App={App} props={props} />);
  },
});
