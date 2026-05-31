import { useCallback, useEffect, useRef, useState } from 'react';

const VIEWPORT = 280;
const OUTPUT_SIZE = 400;

/**
 * Calcule l'échelle de couverture du viewport.
 *
 * @param {number} naturalW Largeur naturelle
 * @param {number} naturalH Hauteur naturelle
 * @returns {number}
 */
function coverScale(naturalW, naturalH) {
  return Math.max(VIEWPORT / naturalW, VIEWPORT / naturalH);
}

/**
 * Modale de recadrage carré pour la photo de profil (fond blanc à l'export).
 *
 * @param {Object} props
 * @param {string} props.imageSrc URL objet de l'image source
 * @param {Function} props.onConfirm Blob image recadrée
 * @param {Function} props.onCancel Annulation
 * @returns {JSX.Element}
 */
export default function AvatarCropModal({ imageSrc, onConfirm, onCancel }) {
  const [scale, setScale] = useState(1);
  const [offset, setOffset] = useState({ x: 0, y: 0 });
  const [dragging, setDragging] = useState(false);
  const [naturalSize, setNaturalSize] = useState({ w: 0, h: 0 });
  const dragStart = useRef({ x: 0, y: 0, ox: 0, oy: 0 });
  const imageRef = useRef(null);

  useEffect(() => {
    setScale(1);
    setOffset({ x: 0, y: 0 });
    setNaturalSize({ w: 0, h: 0 });
  }, [imageSrc]);

  const handleImageLoad = (event) => {
    const image = event.currentTarget;

    setNaturalSize({
      w: image.naturalWidth,
      h: image.naturalHeight,
    });
  };

  const handlePointerDown = (event) => {
    setDragging(true);
    dragStart.current = {
      x: event.clientX,
      y: event.clientY,
      ox: offset.x,
      oy: offset.y,
    };
  };

  const handlePointerMove = (event) => {
    if (!dragging) {
      return;
    }

    setOffset({
      x: dragStart.current.ox + (event.clientX - dragStart.current.x),
      y: dragStart.current.oy + (event.clientY - dragStart.current.y),
    });
  };

  const handlePointerUp = () => {
    setDragging(false);
  };

  const handleConfirm = useCallback(() => {
    const image = imageRef.current;

    if (!image || !image.complete || naturalSize.w === 0) {
      return;
    }

    const canvas = document.createElement('canvas');
    canvas.width = OUTPUT_SIZE;
    canvas.height = OUTPUT_SIZE;
    const ctx = canvas.getContext('2d');

    if (!ctx) {
      return;
    }

    const base = coverScale(naturalSize.w, naturalSize.h);
    const displayScale = base * scale;
    const displayW = naturalSize.w * displayScale;
    const displayH = naturalSize.h * displayScale;
    const imageLeft = VIEWPORT / 2 + offset.x - displayW / 2;
    const imageTop = VIEWPORT / 2 + offset.y - displayH / 2;

    const sourceX = (0 - imageLeft) / displayScale;
    const sourceY = (0 - imageTop) / displayScale;
    const sourceSize = VIEWPORT / displayScale;

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, OUTPUT_SIZE, OUTPUT_SIZE);

    ctx.drawImage(
      image,
      sourceX,
      sourceY,
      sourceSize,
      sourceSize,
      0,
      0,
      OUTPUT_SIZE,
      OUTPUT_SIZE,
    );

    canvas.toBlob(
      (blob) => {
        if (blob) {
          onConfirm(blob);
        }
      },
      'image/jpeg',
      0.92,
    );
  }, [offset, scale, naturalSize, onConfirm]);

  const base = naturalSize.w > 0 ? coverScale(naturalSize.w, naturalSize.h) : 1;
  const displayScale = base * scale;
  const displayW = naturalSize.w * displayScale;
  const displayH = naturalSize.h * displayScale;

  return (
    <div className="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true">
      <div className="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
        <h2 className="font-display text-lg font-bold text-phila-black">Recadrer la photo</h2>
        <p className="mt-1 text-xs text-phila-gray-500">Glissez et zoomez pour cadrer votre visage.</p>

        <div
          className="relative mx-auto mt-4 h-[280px] w-[280px] cursor-move overflow-hidden rounded-full bg-phila-gray-100 ring-2 ring-phila-orange/40"
          onPointerDown={handlePointerDown}
          onPointerMove={handlePointerMove}
          onPointerUp={handlePointerUp}
          onPointerLeave={handlePointerUp}
        >
          <img
            ref={imageRef}
            src={imageSrc}
            alt=""
            draggable={false}
            className="pointer-events-none absolute max-w-none select-none"
            style={
              naturalSize.w > 0
                ? {
                    width: `${displayW}px`,
                    height: `${displayH}px`,
                    left: `${VIEWPORT / 2 + offset.x - displayW / 2}px`,
                    top: `${VIEWPORT / 2 + offset.y - displayH / 2}px`,
                  }
                : {
                    width: '100%',
                    height: '100%',
                    left: 0,
                    top: 0,
                    objectFit: 'cover',
                  }
            }
            onLoad={handleImageLoad}
          />
          {naturalSize.w === 0 && (
            <p className="pointer-events-none absolute inset-0 flex items-center justify-center text-xs text-phila-gray-400">
              Chargement…
            </p>
          )}
        </div>

        <label className="mt-4 block text-xs font-semibold text-phila-gray-600">
          Zoom
          <input
            type="range"
            min="1"
            max="3"
            step="0.01"
            value={scale}
            onChange={(event) => setScale(Number(event.target.value))}
            className="mt-1 w-full accent-phila-orange"
          />
        </label>

        <div className="mt-5 flex gap-2">
          <button type="button" onClick={onCancel} className="btn btn-outline flex-1 py-2.5 text-sm">
            Annuler
          </button>
          <button type="button" onClick={handleConfirm} className="btn btn-accent flex-1 py-2.5 text-sm">
            Appliquer
          </button>
        </div>
      </div>
    </div>
  );
}
