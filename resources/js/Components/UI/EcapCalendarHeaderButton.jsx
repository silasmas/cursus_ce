import { useState } from 'react';
import EcapCalendarModal from './EcapCalendarModal';

/**
 * Bouton calendrier ECAP pour la barre d'en-tête (modale centrée via portail).
 *
 * @param {Object} props
 * @param {Object|null} [props.timeline] Données calendrier
 * @returns {JSX.Element}
 */
export default function EcapCalendarHeaderButton({ timeline }) {
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-phila-gray-200 bg-white text-lg shadow-sm transition hover:border-phila-orange hover:bg-phila-orange-pale"
        aria-label="Calendrier ECAP"
        title="Calendrier ECAP"
      >
        📅
      </button>

      <EcapCalendarModal open={open} onClose={() => setOpen(false)} timeline={timeline} />
    </>
  );
}
