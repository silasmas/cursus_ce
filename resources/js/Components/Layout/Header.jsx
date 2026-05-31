import { Link } from '@inertiajs/react';
import { useRegistrationModal } from './RegistrationModalContext';

/**
 * En-tête public avec logo PHILA et navigation.
 *
 * @param {Object} props
 * @param {boolean} [props.showAuthLinks=true] Afficher connexion/inscription
 * @returns {JSX.Element}
 */
export default function Header({ showAuthLinks = true }) {
  const { openRegistrationInfo, registrationOpen } = useRegistrationModal();

  return (
    <header className="glass-header fixed inset-x-0 top-0 z-50">
      <div className="container-phila flex h-[72px] items-center justify-between">
        <Link href="/" className="flex items-center gap-3">
          <img
            src="/images/phila-logo.png"
            alt="PHILA"
            className="logo-phila-orange h-10 w-10 rounded-full object-cover"
          />
          <div>
            <p className="font-display text-sm font-bold tracking-wide text-phila-black">PHILA-CE</p>
            <p className="text-[10px] uppercase tracking-[0.2em] text-phila-gray-600">Cité d&apos;Exaucement</p>
          </div>
        </Link>

        {showAuthLinks && (
          <nav className="flex items-center gap-2 sm:gap-3">
            <Link href="/connexion" className="btn btn-ghost px-4 py-2">
              Connexion
            </Link>
            <button
              type="button"
              onClick={openRegistrationInfo}
              className={registrationOpen ? 'btn btn-accent px-5 py-2.5 text-sm' : 'btn btn-outline px-5 py-2.5 text-sm'}
            >
              S&apos;inscrire
            </button>
          </nav>
        )}
      </div>
    </header>
  );
}
