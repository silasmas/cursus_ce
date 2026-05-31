import { Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import NotificationBell from '../UI/NotificationBell';
import EcapCalendarHeaderButton from '../UI/EcapCalendarHeaderButton';
import FloatingStaffChatLink from '../UI/FloatingStaffChatLink';
import PortalInstantSearch from './PortalInstantSearch';
import PortalUserMenu from './PortalUserMenu';
import EcapChatUnreadPoller from '../Ecap/EcapChatUnreadPoller';
import { pollJson, startPolling } from '../../lib/pollJson';

/**
 * Entrées de menu selon le rôle ECAP requis.
 */
const NAV_ITEMS = [
  { id: 'questions', href: '/ecap/acteurs/questions', label: 'Questions des fidèles', icon: '💬', roles: null },
  { id: 'messages', href: '/ecap/acteurs/messages', label: 'Messages privés', icon: '📨', roles: ['supervisor', 'moderator'] },
  { id: 'tps', href: '/ecap/acteurs/tp', label: 'TP modèle (enseignant)', icon: '📝', roles: ['teacher'] },
  { id: 'corrections', href: '/ecap/acteurs/corrections-tp', label: 'Corrections TP', icon: '✅', roles: ['supervisor'] },
  {
    id: 'quiz-grading',
    href: '/ecap/acteurs/corrections-quiz',
    label: 'Corrections quiz',
    icon: '📋',
    roles: null,
    requiresQuizGrader: true,
  },
  { id: 'meditation', href: '/ecap/acteurs/meditation', label: 'Cahiers méditation', icon: '📔', roles: ['moderator'] },
];

/**
 * Layout portail acteurs de vacation ECAP (enseignant, superviseur, modérateur).
 *
 * @param {Object} props
 * @param {React.ReactNode} props.children Contenu
 * @param {string} [props.active] Section active
 * @returns {JSX.Element}
 */
export default function EcapStaffLayout({ children, active = 'questions' }) {
  const { auth, notifications, ecapStaffRoles = { keys: [], labels: [], can_grade_quiz: false }, ecapTimeline, ecapStaffChat, ecapPrivateChat } = usePage().props;
  const user = auth?.user;
  const [pendingQuestions, setPendingQuestions] = useState(user?.ecapStaffPendingQuestions ?? 0);
  const [pendingQuizGrading, setPendingQuizGrading] = useState(user?.ecapStaffPendingQuizGrading ?? 0);
  const roleKeys = ecapStaffRoles?.keys ?? [];
  const hideFloatingChat = active === 'messages';
  const [chatUnread, setChatUnread] = useState(ecapStaffChat?.unread_count ?? 0);
  const [chatPulse, setChatPulse] = useState(false);
  const lastChatCountRef = useRef(ecapStaffChat?.unread_count ?? 0);

  const handleChatUnreadChange = useCallback((count) => {
    if (count > lastChatCountRef.current) {
      setChatPulse(true);
      window.setTimeout(() => setChatPulse(false), 4000);
    }

    lastChatCountRef.current = count;
    setChatUnread(count);
  }, []);

  useEffect(() => {
    setPendingQuestions(user?.ecapStaffPendingQuestions ?? 0);
    setPendingQuizGrading(user?.ecapStaffPendingQuizGrading ?? 0);
  }, [user?.ecapStaffPendingQuestions, user?.ecapStaffPendingQuizGrading]);

  useEffect(() => {
    if (!user?.isEcapStaff) {
      return undefined;
    }

    const refreshBadges = async () => {
      const data = await pollJson('/ecap/acteurs/badges');

      if (!data) {
        return;
      }

      if (typeof data.ecapStaffPendingQuestions === 'number') {
        setPendingQuestions(data.ecapStaffPendingQuestions);
      }

      if (typeof data.ecapStaffPendingQuizGrading === 'number') {
        setPendingQuizGrading(data.ecapStaffPendingQuizGrading);
      }
    };

    return startPolling(refreshBadges, 10000, true);
  }, [user?.isEcapStaff]);

  const visibleNav = NAV_ITEMS.filter((item) => {
    if (item.requiresQuizGrader && ecapStaffRoles?.can_grade_quiz !== true) {
      return false;
    }

    if (!item.roles) {
      return true;
    }

    return item.roles.some((role) => roleKeys.includes(role));
  });

  useEffect(() => {
    const html = document.documentElement;
    const body = document.body;
    const previousHtmlOverflow = html.style.overflow;
    const previousBodyOverflow = body.style.overflow;

    html.style.overflow = 'hidden';
    body.style.overflow = 'hidden';

    return () => {
      html.style.overflow = previousHtmlOverflow;
      body.style.overflow = previousBodyOverflow;
    };
  }, []);

  const handleLogout = (event) => {
    event.preventDefault();
    router.post('/deconnexion');
  };

  return (
    <div className="fixed inset-0 flex overflow-hidden bg-phila-gray-50">
      {ecapStaffChat?.enabled && ecapStaffChat?.unread_url && (
        <EcapChatUnreadPoller
          enabled={ecapStaffChat.enabled}
          paused={hideFloatingChat}
          unreadUrl={ecapStaffChat.unread_url}
          onUnreadChange={handleChatUnreadChange}
        />
      )}
      <aside className="z-30 flex h-full w-64 shrink-0 flex-col border-r border-white/10 bg-phila-black text-white">
        <div className="shrink-0 border-b border-white/10 p-5">
          <Link href="/ecap/acteurs/questions" className="flex items-center gap-3">
            <img src="/images/phila-logo.png" alt="PHILA" className="logo-phila-orange h-10 w-10 rounded-full" />
            <div>
              <p className="font-display text-sm font-bold text-phila-orange">Acteurs ECAP</p>
              <p className="text-[10px] text-white/50">Vacation & Q&R</p>
            </div>
          </Link>
        </div>

        {roleKeys.length > 0 && (
          <p className="shrink-0 px-4 pt-3 text-[10px] text-white/50">
            Rôles : {(ecapStaffRoles.labels ?? []).join(', ')}
          </p>
        )}

        <nav className="min-h-0 flex-1 space-y-1 overflow-y-auto p-3">
          {visibleNav.map((item) => (
            <Link
              key={item.id}
              href={item.href}
              className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition ${
                active === item.id
                  ? 'bg-phila-orange text-white shadow-md ring-2 ring-phila-orange/40'
                  : 'text-white/70 hover:bg-white/10 hover:text-white'
              }`}
            >
              <span>{item.icon}</span>
              <span className="flex-1">{item.label}</span>
              {item.id === 'questions' && pendingQuestions > 0 && (
                <span className="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold">
                  {pendingQuestions}
                </span>
              )}
              {item.id === 'messages' && chatUnread > 0 && (
                <span className="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold">
                  {chatUnread}
                </span>
              )}
              {item.id === 'quiz-grading' && pendingQuizGrading > 0 && (
                <span className="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold">
                  {pendingQuizGrading}
                </span>
              )}
            </Link>
          ))}
        </nav>

        <div className="shrink-0 space-y-2 border-t border-white/10 p-4">
          <Link
            href="/mon-espace/profil"
            className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-xs text-white/60 transition hover:bg-white/10 hover:text-white"
          >
            👤 Mon profil
          </Link>
          <Link
            href="/mon-espace"
            className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-xs text-white/60 transition hover:bg-white/10 hover:text-white"
          >
            ← Retour espace fidèle
          </Link>
          <button
            type="button"
            onClick={handleLogout}
            className="w-full rounded-xl border border-white/20 px-3 py-2 text-xs text-white/80 transition hover:border-phila-orange hover:text-phila-orange"
          >
            Déconnexion
          </button>
        </div>
      </aside>

      <div className="flex min-h-0 min-w-0 flex-1 flex-col">
        <header className="z-20 shrink-0 border-b border-phila-gray-100 bg-white/90 px-4 py-2 backdrop-blur-md">
          <div className="flex items-center gap-3">
            <p className="mr-2 hidden shrink-0 font-display text-sm font-bold text-phila-orange sm:block">
              Acteurs ECAP
            </p>
            <div className="min-w-0 flex-1">
              <PortalInstantSearch />
            </div>
            <div className="flex shrink-0 items-center gap-2">
              <EcapCalendarHeaderButton timeline={ecapTimeline} />
              <NotificationBell
                initialNotifications={notifications?.items ?? []}
                initialUnreadCount={notifications?.unread_count ?? 0}
              />
              {user && (
                <PortalUserMenu
                  user={user}
                  ecapStaffRoles={ecapStaffRoles}
                  ecapPrivateChat={ecapPrivateChat}
                  ecapStaffChat={ecapStaffChat}
                  ecapChatUnread={chatUnread}
                />
              )}
            </div>
          </div>
        </header>
        <main className="min-h-0 flex-1 overflow-y-auto overflow-x-hidden overscroll-contain">{children}</main>
      </div>
      {ecapStaffChat?.enabled && !hideFloatingChat && (
        <FloatingStaffChatLink
          href={ecapStaffChat.inbox_url}
          unreadCount={chatUnread}
          unreadUrl={ecapStaffChat.unread_url}
          pulse={chatPulse}
        />
      )}
    </div>
  );
}
