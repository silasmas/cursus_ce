<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
<script>
  window.addEventListener('notificationSent', function (event) {
    const status = event?.detail?.notification?.status;

    if (status !== 'success' || typeof window.confetti !== 'function') {
      return;
    }

    window.confetti({
      particleCount: 120,
      spread: 65,
      origin: { y: 0.72 },
    });
  });
</script>

