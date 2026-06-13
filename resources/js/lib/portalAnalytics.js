/**
 * Charge Plausible ou Google Analytics et suit les pages vues Inertia.
 *
 * @param {{ enabled?: boolean, driver?: string, plausibleDomain?: string|null, plausibleScriptUrl?: string, gaMeasurementId?: string|null }} analytics
 * @returns {void}
 */
export function initPortalAnalytics(analytics) {
  if (!analytics?.enabled) {
    return;
  }

  if (analytics.driver === 'plausible' && analytics.plausibleDomain) {
    loadPlausible(analytics.plausibleDomain, analytics.plausibleScriptUrl);
  }

  if (analytics.driver === 'ga' && analytics.gaMeasurementId) {
    loadGoogleAnalytics(analytics.gaMeasurementId);
  }

  trackPortalPageView(window.location.pathname + window.location.search, analytics);
}

/**
 * Envoie une page vue au provider actif.
 *
 * @param {string} url Chemin de la page
 * @param {{ driver?: string, gaMeasurementId?: string|null }} analytics
 * @returns {void}
 */
export function trackPortalPageView(url, analytics) {
  if (!analytics?.enabled) {
    return;
  }

  if (analytics.driver === 'plausible' && typeof window.plausible === 'function') {
    window.plausible('pageview', { u: url });
  }

  if (analytics.driver === 'ga' && typeof window.gtag === 'function' && analytics.gaMeasurementId) {
    window.gtag('config', analytics.gaMeasurementId, { page_path: url });
  }
}

/**
 * Injecte le script Plausible (respectueux de la vie privée).
 *
 * @param {string} domain Domaine Plausible
 * @param {string} scriptUrl URL du script
 * @returns {void}
 */
function loadPlausible(domain, scriptUrl) {
  if (document.querySelector('script[data-portal-analytics="plausible"]')) {
    return;
  }

  const script = document.createElement('script');
  script.defer = true;
  script.dataset.portalAnalytics = 'plausible';
  script.dataset.domain = domain;
  script.src = scriptUrl || 'https://plausible.io/js/script.js';
  document.head.appendChild(script);
}

/**
 * Injecte gtag.js pour Google Analytics 4.
 *
 * @param {string} measurementId ID de mesure GA4
 * @returns {void}
 */
function loadGoogleAnalytics(measurementId) {
  if (document.querySelector('script[data-portal-analytics="ga"]')) {
    return;
  }

  const loader = document.createElement('script');
  loader.async = true;
  loader.dataset.portalAnalytics = 'ga';
  loader.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
  document.head.appendChild(loader);

  window.dataLayer = window.dataLayer || [];

  window.gtag = function gtag() {
    window.dataLayer.push(arguments);
  };

  window.gtag('js', new Date());
  window.gtag('config', measurementId, { send_page_view: false });
}
