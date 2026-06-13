import { Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

const typeIcons = {
  mentor_message: '💬',
  mentee_message: '💬',
  admin_message: '📢',
  meeting_reminder: '📅',
  level_unlocked: '🎉',
  mentor_approval: '✅',
  mentor_rejection: '⚠️',
  tp_pending: '📋',
  report_unlocked: '📝',
};

/**
 * Cloche de notifications in-app avec actions contextuelles.
 *
 * @param {Object} props
 * @param {Array} props.initialNotifications Notifications initiales
 * @param {number} props.initialUnreadCount Compteur non lues
 * @param {{sound?: boolean, blink?: boolean}} [props.preferences] Préférences signalement
 * @returns {JSX.Element}
 */
export default function NotificationBell({
  initialNotifications = [],
  initialUnreadCount = 0,
  preferences = { sound: true, blink: true },
}) {
  const [open, setOpen] = useState(false);
  const [notifications, setNotifications] = useState(initialNotifications);
  const [unreadCount, setUnreadCount] = useState(initialUnreadCount);
  const [hasNewAlert, setHasNewAlert] = useState(false);
  const panelRef = useRef(null);
  const lastUnreadRef = useRef(initialUnreadCount);
  const blinkTimerRef = useRef(null);

  useEffect(() => {
    setNotifications(initialNotifications);
    setUnreadCount(initialUnreadCount);
  }, [initialNotifications, initialUnreadCount]);

  useEffect(() => {
    const poll = async () => {
      try {
        const response = await fetch('/mon-espace/notifications', {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });

        if (response.ok) {
          const data = await response.json();
          const nextUnread = data.unread_count ?? 0;

          if (nextUnread > lastUnreadRef.current) {
            setHasNewAlert(true);
            window.setTimeout(() => setHasNewAlert(false), 5000);

            if (preferences?.sound) {
              playNotificationBeep();
            }
          }

          lastUnreadRef.current = nextUnread;
          setNotifications(data.notifications ?? []);
          setUnreadCount(nextUnread);
        }
      } catch {
        // silencieux
      }
    };

    const interval = setInterval(poll, 5000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (!preferences?.blink) {
      return undefined;
    }

    if (!hasNewAlert) {
      document.title = document.title.replace(/^●\s/, '');
      if (blinkTimerRef.current) {
        window.clearInterval(blinkTimerRef.current);
        blinkTimerRef.current = null;
      }
      return undefined;
    }

    let active = false;
    blinkTimerRef.current = window.setInterval(() => {
      active = !active;
      document.title = active ? `● ${document.title.replace(/^●\s/, '')}` : document.title.replace(/^●\s/, '');
    }, 700);

    return () => {
      if (blinkTimerRef.current) {
        window.clearInterval(blinkTimerRef.current);
        blinkTimerRef.current = null;
      }
      document.title = document.title.replace(/^●\s/, '');
    };
  }, [hasNewAlert, preferences?.blink]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (panelRef.current && !panelRef.current.contains(event.target)) {
        setOpen(false);
      }
    };

    if (open) {
      document.addEventListener('mousedown', handleClickOutside);
    }

    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [open]);

  const markRead = async (id) => {
    await fetch(`/mon-espace/notifications/${id}/lu`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      credentials: 'same-origin',
    });

    setNotifications((prev) => prev.map((n) => (n.id === id ? { ...n, is_read: true } : n)));
    setUnreadCount((c) => Math.max(0, c - 1));
  };

  const handleOpen = (notification) => {
    markRead(notification.id);
    setOpen(false);

    if (notification.action_url) {
      router.visit(notification.action_url);
    }
  };

  const handleMarkReadOnly = async (event, notification) => {
    event.stopPropagation();

    if (notification.is_read) {
      return;
    }

    await markRead(notification.id);
  };

  return (
    <div className="relative" ref={panelRef}>
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className={`relative flex h-10 w-10 items-center justify-center rounded-full border border-phila-gray-100 bg-white text-lg transition hover:border-phila-orange ${
          hasNewAlert ? 'animate-pulse ring-2 ring-phila-orange/30' : ''
        }`}
        aria-label="Notifications"
      >
        🔔
        {unreadCount > 0 && (
          <span className="absolute -right-0.5 -top-0.5 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-phila-orange px-1 text-[10px] font-bold text-white">
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {open && (
        <div className="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-phila-gray-100 bg-white shadow-xl sm:w-96">
          <div className="border-b border-phila-gray-100 px-4 py-3">
            <p className="font-display font-bold">Notifications</p>
          </div>
          <div className="max-h-96 overflow-y-auto">
            {notifications.length === 0 ? (
              <p className="p-4 text-sm text-phila-gray-500">Aucune notification.</p>
            ) : (
              notifications.map((n) => (
                <div
                  key={n.id}
                  className={`border-b border-phila-gray-50 px-4 py-3 ${n.is_read ? 'opacity-70' : 'bg-phila-orange-pale/30'}`}
                >
                  <div className="flex gap-2">
                    <span className="text-lg">{typeIcons[n.type] ?? '🔔'}</span>
                    <div className="min-w-0 flex-1">
                      <p className="text-sm font-semibold">{n.title}</p>
                      <p className="mt-0.5 text-xs text-phila-gray-600">{n.body}</p>
                      <p className="mt-1 text-[10px] text-phila-gray-400">{n.created_at}</p>
                      {(n.action_url || !n.is_read) && (
                        <div className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                          {n.action_url && (
                            <button
                              type="button"
                              onClick={() => handleOpen(n)}
                              className="text-xs font-semibold text-phila-orange hover:underline"
                            >
                              {n.action_label ?? 'Voir'} →
                            </button>
                          )}
                          {!n.is_read && (
                            <button
                              type="button"
                              onClick={(event) => handleMarkReadOnly(event, n)}
                              className="text-xs font-medium text-phila-gray-500 hover:text-phila-black hover:underline"
                            >
                              Marquer comme lu
                            </button>
                          )}
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        </div>
      )}
    </div>
  );
}

/**
 * Émet un bip léger sans dépendance externe.
 */
function playNotificationBeep() {
  try {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) {
      return;
    }

    const audioContext = new AudioContextClass();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.value = 880;
    gainNode.gain.value = 0.04;

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.start();
    oscillator.stop(audioContext.currentTime + 0.12);
  } catch {
    // Ignore les bloqueurs audio navigateur.
  }
}
