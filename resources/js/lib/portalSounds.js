/**
 * Sons courts pour le chat et les notifications (Web Audio API).
 */

let audioContext = null;

/**
 * @returns {AudioContext|null}
 */
function getContext() {
  if (typeof window === 'undefined') {
    return null;
  }

  if (!audioContext) {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;

    if (!AudioCtx) {
      return null;
    }

    audioContext = new AudioCtx();
  }

  if (audioContext.state === 'suspended') {
    audioContext.resume();
  }

  return audioContext;
}

/**
 * Joue un bip court.
 *
 * @param {number} frequency Fréquence Hz
 * @param {number} duration Durée secondes
 * @param {number} volume Volume 0-1
 */
function playTone(frequency, duration, volume = 0.12) {
  const ctx = getContext();

  if (!ctx) {
    return;
  }

  const oscillator = ctx.createOscillator();
  const gain = ctx.createGain();

  oscillator.type = 'sine';
  oscillator.frequency.value = frequency;
  gain.gain.value = volume;

  oscillator.connect(gain);
  gain.connect(ctx.destination);

  const now = ctx.currentTime;
  oscillator.start(now);
  gain.gain.exponentialRampToValueAtTime(0.001, now + duration);
  oscillator.stop(now + duration);
}

/**
 * Son à l'envoi d'un message.
 */
export function playMessageSentSound() {
  playTone(520, 0.08, 0.1);
  window.setTimeout(() => playTone(680, 0.06, 0.08), 60);
}

/**
 * Son à la réception d'un message.
 */
export function playMessageReceivedSound() {
  playTone(440, 0.1, 0.14);
  window.setTimeout(() => playTone(360, 0.12, 0.1), 90);
}
