import { useState } from 'react';
import EcapCalendarModal from './EcapCalendarModal';

/**
 * Bouton flottant ouvrant le calendrier ECAP en modale centrée.
 *
 * @param {Object} props
 * @param {Object|null} props.timeline Données calendrier
 * @returns {JSX.Element}
 */
export default function FloatingEcapCalendar({ timeline }) {
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="fixed bottom-6 left-6 z-100 flex h-14 w-14 items-center justify-center rounded-full bg-phila-black text-2xl text-white shadow-lg transition hover:scale-105 hover:bg-phila-orange"
        aria-label="Ouvrir le calendrier ECAP"
        title="Calendrier ECAP"
      >
        📅
      </button>

      <EcapCalendarModal open={open} onClose={() => setOpen(false)} timeline={timeline} />
    </>
  );
}
