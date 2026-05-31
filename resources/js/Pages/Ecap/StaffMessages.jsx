import { Head, Link, router } from '@inertiajs/react';
import EcapChatThread from '../../Components/Ecap/EcapChatThread';
import EcapStaffLayout from '../../Components/Layout/EcapStaffLayout';

/**
 * Messagerie acteurs ECAP — liste des conversations + fil (style WhatsApp).
 *
 * @param {Object} props
 * @param {Object} props.inbox Données inbox
 * @returns {JSX.Element}
 */
export default function StaffMessages({ inbox }) {
  const conversations = inbox?.conversations ?? [];
  const activePeerId = inbox?.active_peer_id ?? null;
  const activeConversation = conversations.find((row) => row.id === activePeerId);
  const totalUnread = conversations.reduce((sum, row) => sum + (row.unread_count ?? 0), 0);

  const selectPeer = (peerId) => {
    router.get('/ecap/acteurs/messages', { peer: peerId }, { preserveState: true, preserveScroll: true });
  };

  const markAllRead = async () => {
    if (!inbox?.unread_url || totalUnread === 0) {
      return;
    }

    await fetch(inbox.unread_url.replace('/unread', '/lu'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    });

    router.reload({ preserveScroll: true });
  };

  return (
    <EcapStaffLayout active="messages">
      <Head title="Messages fidèles" />

      <div className="flex h-[calc(100vh-57px)] min-h-0 flex-col bg-[#e5ddd5] md:flex-row">
        <aside className="flex w-full shrink-0 flex-col border-r border-phila-gray-200 bg-white md:w-80 lg:w-96">
          <div className="flex items-center justify-between border-b border-phila-gray-100 bg-phila-black px-4 py-4 text-white">
            <div>
              <p className="font-display text-sm font-bold">Messages</p>
              <p className="text-[10px] text-white/60">{inbox?.session_name ?? 'Session ECAP'}</p>
            </div>
            {totalUnread > 0 && inbox?.unread_url && (
              <button
                type="button"
                onClick={markAllRead}
                className="rounded-full border border-white/20 px-3 py-1 text-[10px] font-semibold text-white/90 transition hover:bg-white/10"
              >
                Tout marquer lu ({totalUnread})
              </button>
            )}
          </div>
          <div className="min-h-0 flex-1 overflow-y-auto">
            {conversations.length === 0 && (
              <p className="p-4 text-xs text-phila-gray-500">
                Aucun fidèle inscrit ou aucune conversation pour le moment.
              </p>
            )}
            {conversations.map((conversation) => {
              const isActive = activePeerId === conversation.id;
              const isUnread = (conversation.unread_count ?? 0) > 0;

              return (
              <button
                key={conversation.id}
                type="button"
                onClick={() => selectPeer(conversation.id)}
                className={`flex w-full items-start gap-3 border-b border-phila-gray-50 px-4 py-3 text-left transition hover:bg-phila-gray-50 ${
                  isActive
                    ? 'bg-phila-orange-pale'
                    : isUnread
                      ? 'bg-emerald-50/80'
                      : 'bg-white'
                }`}
              >
                <Avatar name={conversation.name} url={conversation.avatar_url} unread={isUnread} />
                <div className="min-w-0 flex-1">
                  <div className="flex items-baseline justify-between gap-2">
                    <p className={`truncate text-sm ${isUnread ? 'font-bold text-phila-black' : 'font-semibold text-phila-gray-800'}`}>
                      {conversation.name}
                    </p>
                    <span className={`shrink-0 text-[10px] ${isUnread ? 'font-semibold text-phila-orange' : 'text-phila-gray-400'}`}>
                      {conversation.last_message_at}
                    </span>
                  </div>
                  <p className={`truncate text-xs ${isUnread ? 'font-medium text-phila-black' : 'text-phila-gray-500'}`}>
                    {conversation.last_message_mine ? 'Vous : ' : ''}
                    {conversation.last_message || '—'}
                  </p>
                </div>
                {isUnread && (
                  <span className="mt-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-phila-orange px-1.5 text-[10px] font-bold text-white">
                    {conversation.unread_count}
                  </span>
                )}
              </button>
              );
            })}
          </div>
        </aside>

        <section className="flex min-h-0 min-w-0 flex-1 flex-col bg-[#efeae2]">
          {activePeerId ? (
            <>
              <header className="flex items-center gap-3 border-b border-phila-gray-200 bg-phila-gray-50 px-4 py-3">
                <Avatar name={activeConversation?.name} url={activeConversation?.avatar_url} />
                <div>
                  <p className="font-display text-sm font-bold text-phila-black">{activeConversation?.name}</p>
                  <p className="text-[10px] text-phila-gray-500">Fidèle — session ECAP</p>
                </div>
              </header>
              <EcapChatThread
                key={activePeerId}
                initialMessages={inbox?.messages ?? []}
                peerUserId={activePeerId}
                pollUrl={inbox?.poll_url}
                sendUrl={inbox?.send_url}
                mineVariant="staff"
                className="flex-1"
              />
            </>
          ) : (
            <div className="flex flex-1 flex-col items-center justify-center gap-2 p-8 text-center">
              <span className="text-4xl">💬</span>
              <p className="text-sm text-phila-gray-600">Sélectionnez un fidèle pour afficher la conversation.</p>
              <Link href="/ecap/acteurs/questions" className="text-xs text-phila-orange hover:underline">
                Retour aux questions
              </Link>
            </div>
          )}
        </section>
      </div>
    </EcapStaffLayout>
  );
}

/**
 * Avatar initiales ou photo.
 *
 * @param {Object} props
 * @returns {JSX.Element}
 */
function Avatar({ name, url, unread = false }) {
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
