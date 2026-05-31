/**
 * Requête JSON pour le polling temps réel (portail fidèle / acteurs).
 *
 * @param {string} url URL du flux
 * @returns {Promise<Object|null>} Données JSON ou null si échec
 */
export async function pollJson(url) {
  if (!url) {
    return null;
  }

  try {
    const response = await fetch(url, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    });

    if (!response.ok) {
      return null;
    }

    return await response.json();
  } catch {
    return null;
  }
}

/**
 * Lance un polling périodique avec nettoyage automatique.
 *
 * @param {() => void|Promise<void>} callback Action à exécuter
 * @param {number} intervalMs Intervalle en millisecondes
 * @param {boolean} [enabled=true] Active ou non le polling
 * @returns {() => void} Fonction d'arrêt
 */
export function startPolling(callback, intervalMs, enabled = true) {
  if (!enabled) {
    return () => {};
  }

  const intervalId = window.setInterval(() => {
    callback();
  }, intervalMs);

  return () => window.clearInterval(intervalId);
}
