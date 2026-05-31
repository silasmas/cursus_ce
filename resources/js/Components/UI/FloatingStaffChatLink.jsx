import { Link } from '@inertiajs/react';

/**
 * Bouton flottant vers la messagerie acteurs ECAP (style WhatsApp).
 *
 * @param {Object} props
 * @param {string} props.href URL de la page messages
 * @param {number} [props.unreadCount] Messages non lus
 * @param {boolean} [props.pulse] Animation clignotante
 * @param {string|null} [props.unreadUrl] URL marquer tout lu
 * @returns {JSX.Element}
 */
export default function FloatingStaffChatLink({ href, unreadCount = 0, pulse = false, unreadUrl = null }) {
  const markAllRead = async (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (!unreadUrl || unreadCount === 0) {
      return;
    }

    await fetch(unreadUrl.replace('/unread', '/lu'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    });

    window.location.reload();
  };

  return (
    <div className="fixed bottom-6 right-6 z-[100] flex flex-col items-end gap-2">
      {unreadCount > 0 && unreadUrl && (
        <button
          type="button"
          onClick={markAllRead}
          className="rounded-full border border-phila-gray-200 bg-white px-3 py-1.5 text-[10px] font-semibold text-phila-gray-700 shadow-sm hover:bg-phila-gray-50"
        >
          Tout marquer lu ({unreadCount})
        </button>
      )}
      <Link
        href={href}
        className={`relative flex h-14 w-14 items-center justify-center rounded-full bg-phila-orange text-2xl text-white shadow-lg transition hover:scale-105 hover:bg-phila-orange-hover ${
          pulse || unreadCount > 0 ? 'animate-ecap-chat-pulse ring-4 ring-red-500/40' : ''
        }`}
        aria-label="Ouvrir les messages ECAP"
      >
        💬
        {unreadCount > 0 && (
          <span className="absolute -right-1 -top-1 flex h-5 min-w-[20px] animate-bounce items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </Link>
    </div>
  );
}
