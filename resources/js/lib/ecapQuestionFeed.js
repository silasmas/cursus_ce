/**
 * Envoie une réponse au fil Q&R sans rechargement de page.
 *
 * @param {string} replyUrl URL POST
 * @param {Object} payload Corps { body }
 * @returns {Promise<Array>}
 */
export async function postQuestionReply(replyUrl, payload) {
  const response = await fetch(replyUrl, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    throw new Error('Impossible de publier la réponse.');
  }

  const data = await response.json();

  return data.posts ?? [];
}

/**
 * Met à jour une réponse officielle (historique conservé côté serveur).
 *
 * @param {string} updateUrl URL PATCH
 * @param {Object} payload Corps { body }
 * @returns {Promise<Array>}
 */
export async function patchQuestionReply(updateUrl, payload) {
  const response = await fetch(updateUrl, {
    method: 'PATCH',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    throw new Error('Impossible de modifier la réponse.');
  }

  const data = await response.json();

  return data.posts ?? [];
}

/**
 * Charge les posts du fil Q&R sans recharger la page.
 *
 * @param {string} feedUrl URL de l'API feed
 * @param {Object} filters Filtres module / addressee / author
 * @returns {Promise<Array>}
 */
export async function fetchQuestionFeed(feedUrl, filters = {}) {
  const params = new URLSearchParams();

  if (filters.module) {
    params.set('module', String(filters.module));
  }

  if (filters.addressee) {
    params.set('addressee', String(filters.addressee));
  }

  if (filters.author) {
    params.set('author', String(filters.author));
  }

  const query = params.toString();
  const url = query ? `${feedUrl}?${query}` : feedUrl;

  const response = await fetch(url, {
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
  });

  if (!response.ok) {
    throw new Error('Impossible de charger le fil.');
  }

  const data = await response.json();

  return data.posts ?? [];
}

/**
 * Applique un filtre depuis une URL de mention (?module=…).
 *
 * @param {string} href URL relative
 * @returns {Object}
 */
export function filtersFromHref(href) {
  try {
    const url = new URL(href, window.location.origin);

    return {
      module: url.searchParams.get('module') ? Number(url.searchParams.get('module')) : null,
      addressee: url.searchParams.get('addressee') ? Number(url.searchParams.get('addressee')) : null,
      author: url.searchParams.get('author') ? Number(url.searchParams.get('author')) : null,
    };
  } catch {
    return {};
  }
}

/**
 * Ouvre une page (chapitre, profil) ou applique un filtre du fil Q&R.
 *
 * @param {string} href URL cible
 * @param {Object} options Options navigation
 * @param {import('@inertiajs/react').Router} options.router Routeur Inertia
 * @param {Function} options.applyFilters Applique les filtres du fil
 * @returns {void}
 */
export function navigateMentionHref(href, { router, applyFilters }) {
  if (!href) {
    return;
  }

  if (href.includes('/mon-espace/cours/') || href.includes('/mon-espace/membres/')) {
    router.visit(href);
    return;
  }

  applyFilters(filtersFromHref(href));
}
