<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@11.2.0/dist/css/shepherd.css">
<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11.2.0/dist/js/shepherd.min.js"></script>

<script>
  window.dynamicTourSteps = @json($tourSteps ?? []);
  window.navigationMap = @json($navigationMap ?? []);
  window.customWelcomeStep = @json($welcomeStep ?? null);
  window.customFinishStep = @json($finishStep ?? null);
</script>

@vite('resources/js/filament/admin-tour.js')
