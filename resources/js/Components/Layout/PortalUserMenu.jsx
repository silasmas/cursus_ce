import { Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import UserAvatar from '../UI/UserAvatar';

/**
 * Menu déroulant profil : espaces selon rôles, profil, déconnexion.
 *
 * @param {Object} props
 * @param {Object} props.user Utilisateur auth
 * @param {Object} props.ecapStaffRoles Rôles ECAP
 * @param {Object|null} props.ecapPrivateChat Chat fidèle
 * @param {Object|null} props.ecapStaffChat Chat acteur
 * @param {number} [props.ecapChatUnread] Compteur live messages ECAP (fidèle)
 * @returns {JSX.Element}
 */
export default function PortalUserMenu({ user, ecapStaffRoles, ecapPrivateChat, ecapStaffChat, ecapChatUnread = 0 }) {
  const [open, setOpen] = useState(false);
  const rootRef = useRef(null);

  useEffect(() => {
    const handleClick = (event) => {
      if (rootRef.current && !rootRef.current.contains(event.target)) {
        setOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClick);

    return () => document.removeEventListener('mousedown', handleClick);
  }, []);

  const handleLogout = (event) => {
    event.preventDefault();
    router.post('/deconnexion');
  };

  const spaceLinks = buildSpaceLinks(user, ecapStaffRoles, ecapPrivateChat, ecapStaffChat, ecapChatUnread);
  const roleLabels = ecapStaffRoles?.labels ?? [];

  return (
    <div ref={rootRef} className="relative">
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="flex items-center gap-2 rounded-full border border-phila-gray-200 bg-white py-1 pl-1 pr-2 shadow-sm transition hover:border-phila-orange/40"
        aria-expanded={open}
        aria-haspopup="true"
      >
        <UserAvatar avatarUrl={user?.avatar_url} name={user?.name} sizeClass="h-9 w-9" textClass="text-xs" />
        <span className="hidden max-w-[120px] truncate text-xs font-semibold text-phila-black sm:inline">
          {user?.name?.split(' ')[0] ?? 'Compte'}
        </span>
        <span className="text-[10px] text-phila-gray-400">{open ? '▲' : '▼'}</span>
      </button>

      {open && (
        <div className="absolute right-0 top-full z-[60] mt-2 w-72 overflow-hidden rounded-2xl border border-phila-gray-100 bg-white shadow-xl">
          <div className="border-b border-phila-gray-100 bg-phila-gray-50 px-4 py-3">
            <p className="truncate text-sm font-bold text-phila-black">{user?.name}</p>
            <p className="truncate text-xs text-phila-gray-500">{user?.email}</p>
            {roleLabels.length > 0 && (
              <p className="mt-1 text-[10px] font-semibold uppercase text-phila-orange">
                {roleLabels.join(' · ')}
              </p>
            )}
          </div>

          <div className="max-h-[min(60vh,400px)] overflow-y-auto py-1">
            <p className="px-4 py-2 text-[10px] font-bold uppercase tracking-wide text-phila-gray-400">Mes espaces</p>
            {spaceLinks.map((item) => (
              <MenuLink key={item.href} {...item} onNavigate={() => setOpen(false)} />
            ))}

            <div className="my-1 border-t border-phila-gray-100" />
            <MenuLink href="/mon-espace/profil" icon="👤" label="Mon profil" onNavigate={() => setOpen(false)} />
          </div>

          <div className="border-t border-phila-gray-100 p-2">
            <button
              type="button"
              onClick={handleLogout}
              className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50"
            >
              <span>⎋</span>
              Déconnexion
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

/**
 * @param {Object} props
 * @returns {JSX.Element}
 */
function MenuLink({ href, icon, label, badge, onNavigate }) {
  return (
    <Link
      href={href}
      onClick={onNavigate}
      className="flex items-center gap-2 px-4 py-2.5 text-sm text-phila-black transition hover:bg-phila-orange-pale"
    >
      {icon && <span className="text-base">{icon}</span>}
      <span className="flex-1">{label}</span>
      {badge > 0 && (
        <span className="rounded-full bg-phila-orange px-2 py-0.5 text-[10px] font-bold text-white">{badge}</span>
      )}
    </Link>
  );
}

/**
 * @param {Object} user
 * @param {Object} ecapStaffRoles
 * @param {Object|null} ecapPrivateChat
 * @param {Object|null} ecapStaffChat
 * @param {number} ecapChatUnread
 * @returns {Array}
 */
function buildSpaceLinks(user, ecapStaffRoles, ecapPrivateChat, ecapStaffChat, ecapChatUnread = 0) {
  const links = [{ href: '/mon-espace', icon: '🏠', label: 'Mon espace' }];

  links.push({
    href: '/mon-espace/mes-quiz',
    icon: '📋',
    label: 'Mes quiz',
    badge: user?.studentPendingQuizGrading ?? 0,
  });

  if (user?.isMentee) {
    links.push({ href: '/mon-espace/mentor', icon: '🦋', label: 'Mentoré Métamorpho' });
  }

  if (user?.isMentor) {
    links.push({ href: '/mentor', icon: '★', label: 'Espace Mentor' });

    if ((user.mentorPendingSubmissions ?? 0) > 0) {
      links.push({
        href: '/mentor/soumissions',
        icon: '📋',
        label: 'Soumissions à valider',
        badge: user.mentorPendingSubmissions,
      });
    }
  }

  if (user?.hasEcapSession) {
    links.push({ href: '/mon-espace/ecap/questions', icon: '💬', label: 'Questions ECAP' });
  }

  if (ecapPrivateChat?.enabled) {
    links.push({
      href: '/mon-espace/ecap/messages',
      icon: '📨',
      label: 'Messages ECAP (acteurs)',
      badge: ecapChatUnread || ecapPrivateChat.unread_count || 0,
    });
  }

  if (user?.isEcapStaff) {
    links.push({
      href: '/ecap/acteurs/questions',
      icon: '🎓',
      label: 'Acteurs ECAP',
      badge: user.ecapStaffPendingQuestions ?? 0,
    });

    if (ecapStaffRoles?.can_grade_quiz) {
      links.push({
        href: '/ecap/acteurs/corrections-quiz',
        icon: '📋',
        label: 'Corrections quiz',
        badge: user.ecapStaffPendingQuizGrading ?? 0,
      });
    }
  }

  if (ecapStaffChat?.enabled) {
    links.push({
      href: ecapStaffChat.inbox_url ?? '/ecap/acteurs/messages',
      icon: '📨',
      label: 'Messages fidèles',
      badge: ecapStaffChat.unread_count ?? 0,
    });
  }

  return links;
}

