import { Head, Link } from '@inertiajs/react';
import EcapWhatsAppChat from '../../Components/Ecap/EcapWhatsAppChat';
import AppLayout from '../../Components/Layout/AppLayout';

/**
 * Messagerie fidèle ECAP plein écran (style WhatsApp).
 *
 * @param {Object} props Props Inertia
 * @returns {JSX.Element}
 */
export default function MemberMessages({
  contacts = [],
  contacts_empty: contactsEmpty = false,
  active_peer_id: activePeerId,
  messages = [],
  poll_url: pollUrl,
  send_url: sendUrl,
}) {
  return (
    <AppLayout>
      <Head title="Messages ECAP" />

      <div className="container-phila py-4">
        <Link href="/mon-espace" className="text-sm text-phila-orange hover:underline">
          ← Mon espace
        </Link>
        <div className="mt-3 h-[calc(100vh-140px)] min-h-[480px]">
          <EcapWhatsAppChat
            contacts={contacts}
            contactsEmpty={contactsEmpty}
            initialPeerId={activePeerId}
            initialMessages={messages}
            pollUrl={pollUrl}
            sendUrl={sendUrl}
            title="Messages ECAP"
            subtitle="Vos acteurs de vacation"
            peerSubtitle="Superviseur ou modérateur"
          />
        </div>
      </div>
    </AppLayout>
  );
}
