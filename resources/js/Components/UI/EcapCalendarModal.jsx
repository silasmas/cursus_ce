import { createPortal } from 'react-dom';
import { useEffect } from 'react';
import EcapSessionTimeline from '../Dashboard/EcapSessionTimeline';

/**
 * Modale calendrier ECAP centrée sur l'écran (portail document.body).
 *
 * @param {Object} props
 * @param {boolean} props.open Modale ouverte
 * @param {Function} props.onClose Fermeture
 * @param {Object|null} [props.timeline] Données calendrier
 * @returns {JSX.Element|null}
 */
export default function EcapCalendarModal({ open, onClose, timeline }) {
  useEffect(() => {
    if (!open) {
      return undefined;
    }

    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const onKeyDown = (event) => {
      if (event.key === 'Escape') {
        onClose();
      }
    };

    window.addEventListener('keydown', onKeyDown);

    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener('keydown', onKeyDown);
    };
  }, [open, onClose]);

  if (!open) {
    return null;
  }

  return createPortal(
    <div
      className="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
      role="dialog"
      aria-modal="true"
      aria-labelledby="ecap-calendar-title"
      onClick={onClose}
    >
      <div
        className="relative max-h-[min(90vh,820px)] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white p-4 shadow-2xl sm:p-6"
        onClick={(event) => event.stopPropagation()}
      >
        <div className="sticky top-0 z-10 -mx-4 -mt-4 mb-4 flex items-center justify-between border-b border-phila-gray-100 bg-white px-4 py-3 sm:-mx-6 sm:-mt-6 sm:px-6">
          <div>
            <h2 id="ecap-calendar-title" className="font-display text-lg font-bold text-phila-black">
              Calendrier ECAP
            </h2>
            {timeline?.session_name && (
              <p className="text-xs text-phila-gray-500">{timeline.session_name}</p>
            )}
          </div>
          <button
            type="button"
            onClick={onClose}
            className="rounded-lg px-3 py-1.5 text-sm font-semibold text-phila-gray-600 hover:bg-phila-gray-100"
          >
            Fermer
          </button>
        </div>

        {timeline?.has_session && (timeline?.items?.length ?? 0) > 0 ? (
          <EcapSessionTimeline timeline={timeline} />
        ) : timeline?.has_session ? (
          <div className="rounded-xl border border-dashed border-phila-orange/30 bg-phila-orange-pale/20 px-6 py-10 text-center">
            <p className="text-4xl">📅</p>
            <p className="mt-3 font-semibold text-phila-black">Calendrier en préparation</p>
            <p className="mt-2 text-sm text-phila-gray-600">
              Les dates de la session <strong>{timeline.session_name}</strong> seront publiées prochainement.
            </p>
          </div>
        ) : (
          <div className="rounded-xl border border-dashed border-phila-gray-200 bg-white px-6 py-10 text-center">
            <p className="text-4xl">📅</p>
            <p className="mt-3 font-semibold text-phila-black">Aucune session ECAP détectée</p>
            <p className="mt-2 text-sm text-phila-gray-600">
              Le calendrier sera disponible dès qu&apos;une session ECAP vous sera affectée.
            </p>
          </div>
        )}
      </div>
    </div>,
    document.body,
  );
}
