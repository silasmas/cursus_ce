import { Link, router, usePage } from '@inertiajs/react';
import NotificationBell from '../UI/NotificationBell';

/**
 * Layout dédié au portail mentor — interface distincte du fidèle (sidebar sombre).
 *
 * @param {Object} props
 * @param {React.ReactNode} props.children Contenu
 * @param {string} [props.active] Section active
 * @returns {JSX.Element}
 */
export default function MentorLayout({ children, active = 'dashboard' }) {
  const { auth, notifications } = usePage().props;
  const user = auth?.user;
  const pending = auth?.user?.mentorPendingSubmissions ?? 0;

  const nav = [
    { id: 'dashboard', href: '/mentor', label: 'Tableau de bord', icon: '🏠' },
    { id: 'mentees', href: '/mentor/mentores', label: 'Mes mentorés', icon: '👥' },
    { id: 'forms', href: '/mentor/formulaires', label: 'Formulaires', icon: '📑' },
    { id: 'submissions', href: '/mentor/soumissions', label: 'Corriger les TP', icon: '📋' },
  ];

  const handleLogout = (event) => {
    event.preventDefault();
    router.post('/deconnexion');
  };

  return (
    <div className="flex min-h-screen bg-phila-gray-50">
      <aside className="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-phila-black text-white lg:static">
        <div className="border-b border-white/10 p-5">
          <Link href="/mentor" className="flex items-center gap-3">
            <img src="/images/phila-logo.png" alt="PHILA" className="logo-phila-orange h-10 w-10 rounded-full" />
            <div>
              <p className="font-display text-sm font-bold text-phila-orange">Espace Mentor</p>
              <p className="text-[10px] text-white/50">PHILA-CE</p>
            </div>
          </Link>
        </div>

        <nav className="flex-1 space-y-1 p-3">
          {nav.map((item) => (
            <Link
              key={item.id}
              href={item.href}
              className={`flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition ${
                active === item.id
                  ? 'bg-phila-orange text-white'
                  : 'text-white/70 hover:bg-white/10 hover:text-white'
              }`}
            >
              <span>{item.icon}</span>
              <span className="flex-1">{item.label}</span>
              {item.id === 'submissions' && pending > 0 && (
                <span className="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold">
                  {pending}
                </span>
              )}
            </Link>
          ))}
        </nav>

        <div className="border-t border-white/10 p-4 space-y-2">
          <Link
            href="/mon-espace"
            className="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-xs text-white/60 transition hover:bg-white/10 hover:text-white"
          >
            ← Retour espace fidèle
          </Link>
          <p className="truncate px-3 text-[10px] text-white/40">{user?.email}</p>
          <button
            type="button"
            onClick={handleLogout}
            className="w-full rounded-xl border border-white/20 px-3 py-2 text-xs text-white/80 transition hover:border-phila-orange hover:text-phila-orange"
          >
            Déconnexion
          </button>
        </div>
      </aside>

      <div className="flex min-w-0 flex-1 flex-col lg:ml-0">
        <header className="sticky top-0 z-30 border-b border-phila-gray-100 bg-white/90 px-4 py-3 backdrop-blur-md">
          <div className="flex items-center justify-between">
            <p className="font-display text-sm font-bold text-phila-orange lg:hidden">Espace Mentor</p>
            <div className="flex flex-1 items-center justify-end gap-3 lg:justify-between">
              <p className="hidden font-display text-sm font-bold text-phila-orange lg:block">Espace Mentor</p>
              <div className="flex items-center gap-2">
                <NotificationBell
                  initialNotifications={notifications?.items ?? []}
                  initialUnreadCount={notifications?.unread_count ?? 0}
                />
                <div className="flex gap-2 lg:hidden">
                  {nav.map((item) => (
                    <Link
                      key={item.id}
                      href={item.href}
                      className={`rounded-lg px-3 py-1.5 text-xs font-semibold ${
                        active === item.id ? 'bg-phila-orange text-white' : 'bg-phila-gray-100'
                      }`}
                    >
                      {item.icon}
                    </Link>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </header>
        <main className="flex-1">{children}</main>
      </div>
    </div>
  );
}
