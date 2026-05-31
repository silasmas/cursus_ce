import { useCallback, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { isPublicRegistrationOpen, resolvePublicRegistration } from '../../lib/publicRegistration';
import RegistrationInfoModal from '../Landing/RegistrationInfoModal';
import Footer from './Footer';
import Header from './Header';
import { RegistrationModalContext } from './RegistrationModalContext';

/**
 * Layout pour les pages publiques (landing, auth).
 *
 * @param {Object} props
 * @param {React.ReactNode} props.children Contenu de la page
 * @param {boolean} [props.showAuthLinks=true] Afficher les liens auth dans le header
 * @returns {JSX.Element}
 */
export default function PublicLayout({ children, showAuthLinks = true }) {
  const pageProps = usePage().props;
  const registrationData = resolvePublicRegistration(pageProps);
  const [modalOpen, setModalOpen] = useState(false);
  const registrationOpen = isPublicRegistrationOpen(registrationData);

  const openRegistrationInfo = useCallback(() => {
    if (registrationOpen) {
      window.location.href = '/inscription';

      return;
    }

    setModalOpen(true);
  }, [registrationOpen]);

  return (
    <RegistrationModalContext.Provider value={{ openRegistrationInfo, registrationOpen }}>
      <div className="min-h-screen">
        <Header showAuthLinks={showAuthLinks} />
        <main className="pt-[72px]">{children}</main>
        <Footer />
        <RegistrationInfoModal
          open={modalOpen}
          onClose={() => setModalOpen(false)}
          registration={registrationData}
        />
      </div>
    </RegistrationModalContext.Provider>
  );
}
