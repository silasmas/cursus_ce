import { useState } from 'react';
import MentorChat from './MentorChat';

/**
 * Bouton flottant ouvrant une fenêtre de chat mentor / mentoré.
 *
 * @param {Object} props
 * @param {Array} props.initialMessages Messages initiaux
 * @param {string} props.pollUrl URL polling
 * @param {string} props.sendUrl URL envoi
 * @param {boolean} props.enabled Chat disponible
 * @param {string} props.title Titre de la fenêtre
 * @param {string} [props.placeholder] Placeholder
 * @returns {JSX.Element|null}
 */
export default function FloatingMentorChat({
  initialMessages,
  pollUrl,
  sendUrl,
  enabled,
  title,
  placeholder = 'Écrire un message…',
}) {
  const [open, setOpen] = useState(false);
  const unreadHint = initialMessages?.length ?? 0;

  if (!enabled) {
    return null;
  }

  return (
    <>
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-phila-orange text-2xl text-white shadow-lg transition hover:scale-105 hover:bg-phila-orange-hover"
        aria-label={open ? 'Fermer le chat' : 'Ouvrir le chat'}
      >
        {open ? '✕' : '💬'}
        {!open && unreadHint > 0 && (
          <span className="absolute -right-1 -top-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-phila-black px-1 text-[10px] font-bold text-white">
            {unreadHint > 9 ? '9+' : unreadHint}
          </span>
        )}
      </button>

      {open && (
        <div className="fixed bottom-24 right-6 z-50 flex w-[min(100vw-2rem,380px)] flex-col overflow-hidden rounded-2xl border border-phila-gray-100 bg-white shadow-2xl">
          <div className="flex items-center justify-between border-b border-phila-gray-100 bg-phila-black px-4 py-3 text-white">
            <div>
              <p className="font-display text-sm font-bold">{title}</p>
              <p className="text-[10px] text-white/60">Messagerie instantanée</p>
            </div>
            <button
              type="button"
              onClick={() => setOpen(false)}
              className="rounded-lg px-2 py-1 text-sm hover:bg-white/10"
              aria-label="Fermer"
            >
              ✕
            </button>
          </div>
          <div className="p-4">
            <MentorChat
              initialMessages={initialMessages}
              pollUrl={pollUrl}
              sendUrl={sendUrl}
              enabled={enabled}
              placeholder={placeholder}
              compact
            />
          </div>
        </div>
      )}
    </>
  );
}
