<x-filament-widgets::widget>
  <x-filament::section
    heading="Légende des colonnes d'action"
    description="Chaque interrupteur correspond à un état exclusif : activer l'un remplace les autres. Le badge « Statut » résume l'état effectif côté fidèle."
    compact
  >
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
        <dt class="text-sm font-semibold text-gray-950 dark:text-white">En attente</dt>
        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Le cursus reste verrouillé dans Mon espace (inscription reçue ou cursus fermé).
        </dd>
      </div>
      <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
        <dt class="text-sm font-semibold text-gray-950 dark:text-white">Ouvert</dt>
        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Le fidèle peut suivre le parcours en ligne normalement.
        </dd>
      </div>
      <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
        <dt class="text-sm font-semibold text-gray-950 dark:text-white">À valider</dt>
        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Le fidèle a déclaré avoir déjà suivi ce cursus — validation admin requise.
        </dd>
      </div>
      <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
        <dt class="text-sm font-semibold text-gray-950 dark:text-white">Acquis</dt>
        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Cursus considéré comme terminé ou validé par l'administration.
        </dd>
      </div>
      <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
        <dt class="text-sm font-semibold text-gray-950 dark:text-white">Dispensé</dt>
        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Dispense administrative : le fidèle n'a pas à refaire ce cursus.
        </dd>
      </div>
      <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/5">
        <dt class="text-sm font-semibold text-gray-950 dark:text-white">Statut (badge)</dt>
        <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          Synthèse lisible de l'état actif — utile pour filtrer et repérer les dossiers à traiter.
        </dd>
      </div>
    </dl>
  </x-filament::section>
</x-filament-widgets::widget>
