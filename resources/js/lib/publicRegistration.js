/**
 * Fusionne les props Inertia liées aux inscriptions publiques ECAP.
 *
 * @param {Object} pageProps Props de usePage()
 * @returns {Object|null}
 */
export function resolvePublicRegistration(pageProps) {
  return pageProps.registration ?? pageProps.publicRegistration ?? null;
}

/**
 * Indique si le formulaire d'inscription est accessible.
 *
 * @param {Object|null} registration Données d'inscription
 * @returns {boolean}
 */
export function isPublicRegistrationOpen(registration) {
  return registration?.is_open === true;
}
