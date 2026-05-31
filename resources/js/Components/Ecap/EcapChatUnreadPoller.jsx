import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

const POLL_MS = 3000;

/**
 * Met à jour le compteur de messages ECAP non lus (hors page chat).
 *
 * @param {Object} props
 * @param {boolean} props.enabled Polling actif
 * @param {boolean} props.paused Pause (utilisateur dans le chat)
 * @param {string} props.unreadUrl URL JSON du compteur
 * @param {Function} props.onUnreadChange Callback (count)
 * @returns {null}
 */
export default function EcapChatUnreadPoller({ enabled, paused, unreadUrl, onUnreadChange }) {
  const lastCountRef = useRef(0);
  const [hasNewPulse, setHasNewPulse] = useState(false);
  const { url: pageUrl } = usePage();

  useEffect(() => {
    if (!enabled || paused || !unreadUrl) {
      return undefined;
    }

    const poll = async () => {
      try {
        const response = await fetch(unreadUrl, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });

        if (!response.ok) {
          return;
        }

        const data = await response.json();
        const count = data.unread_count ?? 0;

        if (count > lastCountRef.current) {
          setHasNewPulse(true);
          window.setTimeout(() => setHasNewPulse(false), 4000);
        }

        lastCountRef.current = count;
        onUnreadChange?.(count, data.contacts ?? []);

        if (data.reload_inertia && !pageUrl.includes('/messages')) {
          router.reload({ only: ['ecapPrivateChat', 'ecapStaffChat'], preserveScroll: true });
        }
      } catch {
        // Ignore réseau intermittente.
      }
    };

    poll();
    const interval = window.setInterval(poll, POLL_MS);

    return () => window.clearInterval(interval);
  }, [enabled, paused, unreadUrl, onUnreadChange, pageUrl]);

  useEffect(() => {
    if (hasNewPulse) {
      document.title = document.title.startsWith('● ')
        ? document.title
        : `● ${document.title}`;
    } else {
      document.title = document.title.replace(/^●\s/, '');
    }
  }, [hasNewPulse]);

  return null;
}
