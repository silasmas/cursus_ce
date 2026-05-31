import { Link, usePage } from '@inertiajs/react';
import { useCallback, useRef, useState } from 'react';
import NotificationBell from '../UI/NotificationBell';
import FloatingEcapChat from '../UI/FloatingEcapChat';
import FloatingEcapCalendar from '../UI/FloatingEcapCalendar';
import PortalInstantSearch from './PortalInstantSearch';
import PortalUserMenu from './PortalUserMenu';
import EcapChatUnreadPoller from '../Ecap/EcapChatUnreadPoller';

/**
 * Layout pour l'espace connecté du fidèle (et menu mentor si applicable).
 *
 * @param {Object} props
 * @param {React.ReactNode} props.children Contenu du dashboard
 * @returns {JSX.Element}
 */
export default function AppLayout({ children }) {
  const page = usePage();
  const { auth, notifications, ecapPrivateChat, ecapTimeline, ecapStaffRoles, ecapStaffChat } = page.props;
  const pageUrl = page.url ?? '';
  const hideFloatingChat = pageUrl.includes('/ecap/messages');
  const [chatUnread, setChatUnread] = useState(ecapPrivateChat?.unread_count ?? 0);
  const [chatPulse, setChatPulse] = useState(false);
  const lastChatCountRef = useRef(ecapPrivateChat?.unread_count ?? 0);

  const handleChatUnreadChange = useCallback((count) => {
    if (count > lastChatCountRef.current) {
      setChatPulse(true);
      window.setTimeout(() => setChatPulse(false), 4000);
    }

    lastChatCountRef.current = count;
    setChatUnread(count);
  }, []);

  const chatUnreadUrl = ecapPrivateChat?.unread_url ?? ecapStaffChat?.unread_url ?? null;
  const chatEnabled = ecapPrivateChat?.enabled || ecapStaffChat?.enabled;

  return (
    <div className="min-h-screen bg-phila-gray-50">
      {chatEnabled && chatUnreadUrl && (
        <EcapChatUnreadPoller
          enabled={chatEnabled}
          paused={hideFloatingChat}
          unreadUrl={chatUnreadUrl}
          onUnreadChange={handleChatUnreadChange}
        />
      )}
      <header className="glass-header sticky top-0 z-50">
        <div className="container-phila flex h-[72px] items-center gap-3">
          <Link href="/mon-espace" className="flex shrink-0 items-center gap-2 sm:gap-3">
            <img src="/images/phila-logo.png" alt="PHILA" className="logo-phila-orange h-9 w-9 rounded-full" />
            <span className="hidden font-display text-sm font-bold sm:inline">Mon Espace</span>
          </Link>

          <PortalInstantSearch />

          <div className="flex shrink-0 items-center gap-2 sm:gap-3">
            <NotificationBell
              initialNotifications={notifications?.items ?? []}
              initialUnreadCount={notifications?.unread_count ?? 0}
            />
            <PortalUserMenu
              user={auth?.user}
              ecapStaffRoles={ecapStaffRoles}
              ecapPrivateChat={ecapPrivateChat}
              ecapStaffChat={ecapStaffChat}
              ecapChatUnread={chatUnread}
            />
          </div>
        </div>
      </header>
      <main>{children}</main>
      <FloatingEcapCalendar timeline={ecapTimeline} />
      {ecapPrivateChat?.enabled && !hideFloatingChat && (
        <FloatingEcapChat
          enabled={ecapPrivateChat.enabled}
          contacts={ecapPrivateChat.contacts ?? []}
          contactsEmpty={ecapPrivateChat.contacts_empty === true}
          initialPeerId={ecapPrivateChat.initial_peer_id}
          initialMessages={ecapPrivateChat.initial_messages ?? []}
          pollUrl={ecapPrivateChat.poll_url}
          sendUrl={ecapPrivateChat.send_url}
          unreadCount={chatUnread}
          unreadUrl={ecapPrivateChat.unread_url}
          pulse={chatPulse}
        />
      )}
    </div>
  );
}
