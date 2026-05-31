import { useCallback, useMemo, useRef, useState } from 'react';
import ReactPlayer from 'react-player';

/**
 * Icône lecture PHILA pour le mode light de ReactPlayer.
 */
function PhilaPlayIcon() {
  return (
    <span className="flex h-16 w-16 items-center justify-center rounded-full bg-phila-orange/90 text-white shadow-lg transition hover:scale-105 hover:bg-phila-orange">
      <svg viewBox="0 0 24 24" className="ml-1 h-8 w-8 fill-current" aria-hidden="true">
        <path d="M8 5v14l11-7z" />
      </svg>
    </span>
  );
}

/**
 * Lecteur vidéo : YouTube via react-player (API iframe), repli MP4 hébergé sur la plateforme.
 *
 * @param {Object} props
 * @param {string|null} props.youtubeUrl Lien watch YouTube
 * @param {string|null} props.streamUrl URL de streaming authentifiée (MP4 local)
 * @param {string|null} props.posterUrl Vignette avant lecture YouTube
 * @param {string} props.title Titre accessible
 * @param {string} props.chapterTitle Titre du chapitre
 */
export default function CourseVideoPlayer({
  youtubeUrl = null,
  streamUrl = null,
  posterUrl = null,
  title = '',
  chapterTitle = '',
}) {
  const playerRef = useRef(null);
  const [youtubeFailed, setYoutubeFailed] = useState(false);

  const canUseYoutube = Boolean(
    youtubeUrl && !youtubeFailed && ReactPlayer.canPlay(youtubeUrl),
  );

  const src = useMemo(() => {
    if (canUseYoutube) {
      return youtubeUrl;
    }

    return streamUrl || null;
  }, [canUseYoutube, streamUrl, youtubeUrl]);

  const handleError = useCallback(() => {
    if (canUseYoutube && streamUrl) {
      setYoutubeFailed(true);
    }
  }, [canUseYoutube, streamUrl]);

  const origin = typeof window !== 'undefined' ? window.location.origin : undefined;

  if (!src) {
    return (
      <div className="flex aspect-video w-full flex-col items-center justify-center gap-3 bg-phila-black px-6 text-center text-white/80">
        <img src="/images/phila-logo.png" alt="" className="logo-phila-orange h-16 w-16 rounded-full opacity-40" />
        <p className="text-sm font-medium">La vidéo de cette étape n&apos;est pas encore disponible.</p>
      </div>
    );
  }

  return (
    <div className="relative aspect-video w-full overflow-hidden bg-phila-black">
      <ReactPlayer
        ref={playerRef}
        src={src}
        controls
        width="100%"
        height="100%"
        playsInline
        light={canUseYoutube ? (posterUrl || true) : false}
        playIcon={<PhilaPlayIcon />}
        previewAriaLabel={`Lire la vidéo : ${title || chapterTitle}`}
        onError={handleError}
        config={{
          youtube: {
            rel: 0,
            fs: 1,
            enablejsapi: 1,
            origin,
            widget_referrer: origin,
            referrerpolicy: 'strict-origin-when-cross-origin',
          },
        }}
        style={{ position: 'absolute', inset: 0 }}
      />
    </div>
  );
}
