import { useEffect, useState } from 'react';
import EcapChatThread from './EcapChatThread';

/**
 * Interface chat type WhatsApp (liste contacts + fil).
 *
 * @param {Object} props
 * @param {Array} props.contacts Liste des interlocuteurs
 * @param {boolean} props.contactsEmpty Aucun contact
 * @param {number|null} props.initialPeerId Contact actif initial
 * @param {Array} props.initialMessages Messages du fil actif
 * @param {string} props.pollUrl URL polling
 * @param {string} props.sendUrl URL envoi
 * @param {string} [props.title] Titre panneau gauche
 * @param {string} [props.subtitle] Sous-titre
 * @param {string} [props.peerSubtitle] Sous-titre interlocuteur actif
 * @param {Function} [props.onClose] Fermeture (mode flottant)
 * @returns {JSX.Element}
 */
export default function EcapWhatsAppChat({
  contacts = [],
  contactsEmpty = false,
  initialPeerId = null,
  initialMessages = [],
  pollUrl,
  sendUrl,
  title = 'Messages',
  subtitle = '',
  peerSubtitle = '',
  onClose,
}) {
  const [activePeerId, setActivePeerId] = useState(initialPeerId ?? contacts[0]?.id ?? null);
  const [threadMessages, setThreadMessages] = useState(initialMessages);

  useEffect(() => {
    const nextPeer = initialPeerId ?? contacts[0]?.id ?? null;
    setActivePeerId(nextPeer);
  }, [contacts, initialPeerId]);

  useEffect(() => {
    setThreadMessages(initialMessages);
  }, [initialMessages, activePeerId]);

  const activeContact = contacts.find((row) => row.id === activePeerId);

  const selectPeer = async (peerId) => {
    setActivePeerId(peerId);

    if (!pollUrl) {
      return;
    }

    const response = await fetch(`${pollUrl}?peer=${peerId}`, {
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    });

    if (response.ok) {
      const data = await response.json();
      setThreadMessages(data.messages ?? []);
    }
  };

  return (
    <div className="flex h-full min-h-0 flex-col overflow-hidden rounded-2xl border border-phila-gray-100 bg-white shadow-2xl md:flex-row">
      <aside className="flex w-full shrink-0 flex-col border-b border-phila-gray-200 bg-white md:w-72 md:border-b-0 md:border-r lg:w-80">
        <div className="flex items-center justify-between border-b border-phila-gray-100 bg-phila-black px-4 py-3 text-white">
          <div>
            <p className="font-display text-sm font-bold">{title}</p>
            {subtitle && <p className="text-[10px] text-white/60">{subtitle}</p>}
          </div>
          {onClose && (
            <button type="button" onClick={onClose} className="rounded-lg px-2 py-1 text-lg hover:bg-white/10" aria-label="Fermer">
              ✕
            </button>
          )}
        </div>
        <div className="min-h-0 flex-1 overflow-y-auto">
          {contactsEmpty && (
            <p className="p-4 text-xs text-phila-gray-500">
              Aucun superviseur ou modérateur n&apos;est encore affecté à votre session.
            </p>
          )}
          {!contactsEmpty &&
            contacts.map((contact) => {
              const isActive = activePeerId === contact.id;
              const isUnread = (contact.unread_count ?? 0) > 0;

              return (
              <button
                key={contact.id}
                type="button"
                onClick={() => selectPeer(contact.id)}
                className={`flex w-full items-center gap-3 border-b border-phila-gray-50 px-4 py-3 text-left transition hover:bg-phila-gray-50 ${
                  isActive
                    ? 'bg-phila-orange-pale'
                    : isUnread
                      ? 'bg-emerald-50/80'
                      : 'bg-white'
                }`}
              >
                <ContactAvatar name={contact.name} url={contact.avatar_url} unread={isUnread} />
                <div className="min-w-0 flex-1">
                  <p className={`truncate text-sm ${isUnread ? 'font-bold text-phila-black' : 'font-semibold text-phila-gray-800'}`}>
                    {contact.name}
                  </p>
                  <p className="truncate text-[10px] text-phila-gray-500">{contact.role}</p>
                </div>
                {isUnread && (
                  <span className="flex h-5 min-w-[20px] items-center justify-center rounded-full bg-phila-orange px-1.5 text-[10px] font-bold text-white">
                    {contact.unread_count}
                  </span>
                )}
              </button>
              );
            })}
        </div>
      </aside>

      <section className="flex min-h-0 min-w-0 flex-1 flex-col bg-[#efeae2]">
        {activePeerId && !contactsEmpty ? (
          <>
            <header className="flex items-center gap-3 border-b border-phila-gray-200 bg-phila-gray-50 px-4 py-3">
              <ContactAvatar name={activeContact?.name} url={activeContact?.avatar_url} />
              <div>
                <p className="font-display text-sm font-bold text-phila-black">{activeContact?.name}</p>
                <p className="text-[10px] text-phila-gray-500">{peerSubtitle || activeContact?.role}</p>
              </div>
            </header>
            <EcapChatThread
              key={activePeerId}
              initialMessages={threadMessages}
              peerUserId={activePeerId}
              pollUrl={pollUrl}
              sendUrl={sendUrl}
              className="flex-1"
            />
          </>
        ) : (
          <div className="flex flex-1 flex-col items-center justify-center gap-2 p-8 text-center text-sm text-phila-gray-500">
            <span className="text-3xl">💬</span>
            <p>Sélectionnez un interlocuteur pour démarrer la conversation.</p>
          </div>
        )}
      </section>
    </div>
  );
}

/**
 * @param {Object} props
 * @returns {JSX.Element}
 */
function ContactAvatar({ name, url, unread = false }) {
  const initials = (name ?? 'U')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase();

  if (url) {
    return (
      <img
        src={url}
        alt=""
        className={`h-11 w-11 shrink-0 rounded-full object-cover ${unread ? 'ring-2 ring-phila-orange' : ''}`}
      />
    );
  }

  return (
    <span className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold ${
      unread ? 'bg-phila-orange text-white' : 'bg-phila-orange/20 text-phila-orange'
    }`}
    >
      {initials}
    </span>
  );
}
