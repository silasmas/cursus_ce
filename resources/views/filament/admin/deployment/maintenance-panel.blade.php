@php
  /** @var string $description */
  /** @var int $pendingCount */
  /** @var \Illuminate\Support\Collection<int, array{name: string, status: string, batch: int|null}> $migrations */
  /** @var array<int, array{label: string, badge: string, color: string, hint: string}> $statusItems */
  /** @var bool $migrationsExpandedDefault */
  /** @var array<string, array{label: string, items: array<int, array<string, mixed>>}> $seederGroups */
  /** @var array<string, string> $seederConfirms */
  /** @var array{enabled: bool, url: string, steps: list<string>, seederKey: string, rateLimit: int, curlFull: string, curlCustom: string, curlInfo: string} $httpDeploy */
@endphp

<div class="fi-section col-span-full rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
  {{-- Description pleine largeur --}}
  <div class="border-b border-gray-200 px-6 py-5 dark:border-white/10">
    <p class="max-w-none text-sm leading-relaxed text-gray-600 dark:text-gray-300">
      {{ $description }}
    </p>
  </div>

  {{-- Boutons alignés en dessous, retour à la ligne --}}
  <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
    <div class="flex flex-wrap gap-2">
      <x-filament::button
        wire:click="refreshMigrationStatus"
        color="gray"
        icon="heroicon-o-arrow-path"
        size="sm"
      >
        Actualiser l'état
      </x-filament::button>

      <x-filament::button
        wire:click="runMigrations"
        wire:confirm="Exécuter les migrations en attente ? Équivalent de php artisan migrate --force."
        color="primary"
        icon="heroicon-o-server-stack"
        size="sm"
      >
        Exécuter les migrations
      </x-filament::button>

      <x-filament::button
        wire:click="runShieldGenerate"
        wire:confirm="Régénérer les permissions Filament Shield pour le panel admin ?"
        color="info"
        icon="heroicon-o-shield-check"
        size="sm"
      >
        Générer permissions Shield
      </x-filament::button>

      <x-filament::button
        wire:click="prepareStorageDirectory"
        wire:confirm="Créer le dossier storage/app/public s'il n'existe pas encore ?"
        color="gray"
        icon="heroicon-o-folder-plus"
        size="sm"
      >
        Créer storage/app/public
      </x-filament::button>

      <x-filament::button
        wire:click="runStorageLink"
        wire:confirm="Créer le lien symbolique public/storage ? Équivalent de php artisan storage:link --force."
        color="success"
        icon="heroicon-o-link"
        size="sm"
      >
        Lien public/storage
      </x-filament::button>

      <x-filament::button
        wire:click="setupPublicStorage"
        wire:confirm="Préparer complètement le stockage public (dossier + lien) ?"
        color="warning"
        icon="heroicon-o-cloud-arrow-up"
        size="sm"
      >
        Tout préparer (storage)
      </x-filament::button>
    </div>
  </div>

  {{-- Seeders de démarrage production --}}
  <div class="border-b border-gray-200 px-6 py-5 dark:border-white/10">
    <h4 class="text-sm font-semibold text-gray-950 dark:text-white">
      Données de démarrage (seeders)
    </h4>
    <p class="mt-1 max-w-none text-xs leading-relaxed text-gray-500 dark:text-gray-400">
      Chargez les cursus, la session ECAP 20, le calendrier et les réglages de base. Chaque action est idempotente et journalisée ci-dessous.
    </p>

    @foreach ($seederGroups as $groupKey => $group)
      @if (count($group['items']) === 0)
        @continue
      @endif

      <div class="mt-4">
        <div class="mb-2 flex items-center gap-2">
          <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ $group['label'] }}
          </span>
          @if ($groupKey === 'demo')
            <x-filament::badge color="danger">
              Test uniquement
            </x-filament::badge>
          @endif
        </div>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
          @foreach ($group['items'] as $seeder)
            <div class="flex flex-col justify-between gap-3 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 sm:flex-row sm:items-center">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-950 dark:text-white">
                  {{ $seeder['label'] }}
                </p>
                <p class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                  {{ $seeder['description'] ?? '' }}
                </p>
              </div>
              <x-filament::button
                wire:click="runProductionSeeder('{{ $seeder['key'] }}')"
                wire:confirm="{{ $seederConfirms[$seeder['key']] ?? 'Exécuter ce seeder ?' }}"
                :color="$seeder['color'] ?? 'gray'"
                :icon="$seeder['icon'] ?? 'heroicon-o-play'"
                size="sm"
                class="shrink-0"
              >
                Exécuter
              </x-filament::button>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>

  {{-- Route HTTP CI/CD --}}
  <div
    x-data="{
      copiedKey: null,
      copy(key, text) {
        navigator.clipboard.writeText(text);
        this.copiedKey = key;
        setTimeout(() => { if (this.copiedKey === key) { this.copiedKey = null; } }, 2000);
      },
    }"
    class="border-b border-gray-200 px-6 py-5 dark:border-white/10"
  >
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">
          Déploiement via route HTTP (CI/CD)
        </h4>
        <p class="mt-1 max-w-none text-xs leading-relaxed text-gray-500 dark:text-gray-400">
          Alternative sans SSH pour enchaîner stockage, migrations, seeder
          <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-[11px] dark:bg-white/10">{{ $httpDeploy['seederKey'] }}</code>
          et Shield depuis un pipeline ou un terminal.
        </p>
      </div>
      <x-filament::badge :color="$httpDeploy['enabled'] ? 'success' : 'danger'">
        {{ $httpDeploy['enabled'] ? 'Route active' : 'Route désactivée' }}
      </x-filament::badge>
    </div>

    @if (! $httpDeploy['enabled'])
      <div class="mt-4 rounded-lg bg-danger-50 p-4 ring-1 ring-danger-600/10 dark:bg-danger-500/10 dark:ring-danger-500/20">
        <p class="text-sm text-danger-700 dark:text-danger-400">
          Définissez <code class="font-mono text-xs">DEPLOYMENT_TOKEN</code> dans le fichier <code class="font-mono text-xs">.env</code> pour activer la route.
          Générez un jeton avec :
          <code class="mt-1 block font-mono text-xs">php -r "echo bin2hex(random_bytes(32));"</code>
        </p>
      </div>
    @else
      <dl class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
          <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">URL directe (public/)</dt>
          <dd class="mt-1 truncate font-mono text-xs text-gray-950 dark:text-white" title="{{ $httpDeploy['url'] }}">
            {{ $httpDeploy['url'] }}
          </dd>
        </div>
        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
          <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Route Laravel (optionnelle)</dt>
          <dd class="mt-1 truncate font-mono text-xs text-gray-950 dark:text-white" title="{{ $httpDeploy['routeUrl'] ?? '' }}">
            {{ $httpDeploy['routeUrl'] ?? '—' }}
          </dd>
        </div>
        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
          <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Authentification</dt>
          <dd class="mt-1 text-xs text-gray-950 dark:text-white">
            Paramètre <code class="font-mono">?token=</code> dans l'URL
          </dd>
        </div>
        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
          <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Étapes exécutées</dt>
          <dd class="mt-1 text-xs text-gray-950 dark:text-white">
            {{ implode(' → ', $httpDeploy['steps']) }}
          </dd>
        </div>
      </dl>

      @foreach ([
        'full' => ['label' => 'Pipeline complet', 'command' => $httpDeploy['curlFull']],
        'custom' => ['label' => 'Étapes personnalisées (ex. migrate + shield)', 'command' => $httpDeploy['curlCustom']],
        'info' => ['label' => 'Navigateur ou cron hébergeur (GET)', 'command' => $httpDeploy['curlInfo']],
      ] as $curlKey => $curlBlock)
        <div class="mt-4">
          <div class="mb-2 flex items-center justify-between gap-2">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              {{ $curlBlock['label'] }}
            </p>
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10"
              x-on:click="copy('{{ $curlKey }}', @js($curlBlock['command']))"
            >
              <x-filament::icon icon="heroicon-m-clipboard-document" class="h-4 w-4" />
              <span x-text="copiedKey === '{{ $curlKey }}' ? 'Copié' : 'Copier'"></span>
            </button>
          </div>
          <pre class="deployment-http-code overflow-x-auto rounded-lg bg-gray-950 px-4 py-3 text-xs leading-relaxed text-gray-100"><code>{{ $curlBlock['command'] }}</code></pre>
        </div>
      @endforeach

      <p class="mt-4 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
        Variables <code class="font-mono">.env</code> :
        <code class="font-mono">DEPLOYMENT_TOKEN</code>,
        <code class="font-mono">DEPLOYMENT_ROUTE</code>,
        <code class="font-mono">DEPLOYMENT_SEEDER_KEY</code>.
        Remplacez <code class="font-mono">VOTRE_TOKEN</code> par la valeur secrète — ne la commitez jamais.
      </p>
    @endif
  </div>

  {{-- Badges d'état --}}
  <div class="border-b border-gray-200 px-6 py-5 dark:border-white/10">
    <h4 class="mb-3 text-sm font-semibold text-gray-950 dark:text-white">
      État production
    </h4>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
      @foreach ($statusItems as $item)
        <div class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ $item['label'] }}
          </p>
          <div class="mt-2">
            <x-filament::badge :color="$item['color']">
              {{ $item['badge'] }}
            </x-filament::badge>
          </div>
          <p
            class="mt-2 truncate text-xs text-gray-500 dark:text-gray-400"
            title="{{ $item['hint'] }}"
          >
            {{ $item['hint'] }}
          </p>
        </div>
      @endforeach
    </div>
  </div>

  {{-- Tableau des migrations pliable --}}
  <div
    x-data="{ expanded: @js($migrationsExpandedDefault) }"
    class="px-6 py-4"
  >
    <button
      type="button"
      class="flex w-full items-center justify-between gap-3 rounded-lg px-1 py-2 text-start transition hover:bg-gray-50 dark:hover:bg-white/5"
      x-on:click="expanded = ! expanded"
      x-bind:aria-expanded="expanded"
    >
      <span class="min-w-0 flex-1">
        <span class="block text-sm font-semibold text-gray-950 dark:text-white">
          Liste des migrations
        </span>
        <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">
          {{ $pendingCount }} en attente sur {{ $migrations->count() }} fichier(s)
        </span>
      </span>
      <span class="flex shrink-0 items-center gap-2">
        @if ($pendingCount > 0)
          <x-filament::badge color="warning">
            {{ $pendingCount }} en attente
          </x-filament::badge>
        @else
          <x-filament::badge color="success">
            À jour
          </x-filament::badge>
        @endif
        <x-filament::icon
          icon="heroicon-m-chevron-down"
          class="h-5 w-5 text-gray-400 transition"
          x-bind:class="{ 'rotate-180': expanded }"
        />
      </span>
    </button>

    <div x-show="expanded" x-collapse class="mt-3 overflow-hidden">
      <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
        <table class="fi-ta-table w-full min-w-[32rem] table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
          <thead class="divide-y divide-gray-200 dark:divide-white/5">
            <tr class="bg-gray-50 dark:bg-white/5">
              <th class="fi-ta-header-cell px-4 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">
                Migration
              </th>
              <th class="fi-ta-header-cell px-4 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">
                Statut
              </th>
              <th class="fi-ta-header-cell px-4 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white">
                Lot
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-white/5">
            @forelse ($migrations as $migration)
              <tr class="fi-ta-row">
                <td class="fi-ta-cell max-w-md truncate px-4 py-3 font-mono text-sm text-gray-950 dark:text-white" title="{{ $migration['name'] }}">
                  {{ $migration['name'] }}
                </td>
                <td class="fi-ta-cell px-4 py-3 text-sm">
                  @if ($migration['status'] === 'executed')
                    <x-filament::badge color="success">
                      Exécutée
                    </x-filament::badge>
                  @else
                    <x-filament::badge color="warning">
                      En attente
                    </x-filament::badge>
                  @endif
                </td>
                <td class="fi-ta-cell px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                  {{ $migration['batch'] ?? '—' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                  Aucune migration trouvée.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
