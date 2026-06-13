<?php

namespace App\Filament\Resources\DeploymentOperations\Widgets;

use App\Filament\Concerns\ExecutesDeploymentOperations;
use App\Filament\Concerns\SendsFilamentOperationFeedback;
use App\Services\System\ProductionSeederService;
use App\Services\System\SystemDeploymentService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

/**
 * Panneau de maintenance production : description, actions, badges et migrations pliables.
 */
class DeploymentMaintenancePanelWidget extends Widget
{
  use ExecutesDeploymentOperations;
  use SendsFilamentOperationFeedback;

  protected static ?int $sort = 0;

  protected int | string | array $columnSpan = 'full';

  /**
   * @var view-string
   */
  protected string $view = 'filament.admin.deployment.maintenance-panel';

  /**
   * Données passées à la vue Blade.
   *
   * @return array<string, mixed>
   */
  protected function getViewData(): array
  {
    $service = app(SystemDeploymentService::class);
    $storage = $service->storageLinkStatus();
    $pendingCount = $service->pendingMigrationCount();
    $environment = app()->environment();

    return [
      'description' => 'Exécutez les migrations, régénérez les permissions Shield, préparez le stockage public et chargez les données de démarrage (cursus, session ECAP) — réservé aux super administrateurs.',
      'pendingCount' => $pendingCount,
      'migrations' => $service->migrationStatuses(),
      'statusItems' => [
        [
          'label' => 'Environnement',
          'badge' => strtoupper($environment),
          'color' => $environment === 'production' ? 'warning' : 'gray',
          'hint' => 'Application Laravel',
        ],
        [
          'label' => 'Migrations',
          'badge' => $pendingCount > 0 ? $pendingCount.' en attente' : 'Base à jour',
          'color' => $pendingCount > 0 ? 'warning' : 'success',
          'hint' => $pendingCount > 0 ? 'Exécution recommandée avant mise en ligne' : 'Toutes les migrations sont appliquées',
        ],
        [
          'label' => 'Dossier storage/app/public',
          'badge' => $storage['target_exists'] ? 'Présent' : 'Absent',
          'color' => $storage['target_exists'] ? 'success' : 'danger',
          'hint' => $storage['target_path'],
        ],
        [
          'label' => 'Lien public/storage',
          'badge' => $storage['is_ready'] ? 'Actif' : 'Manquant',
          'color' => $storage['is_ready'] ? 'success' : 'warning',
          'hint' => $storage['is_symlink'] ? 'Lien symbolique' : ($storage['link_exists'] ? 'Chemin présent' : 'À créer via le bouton ci-dessous'),
        ],
        [
          'label' => 'URL fichiers publics',
          'badge' => 'storage/',
          'color' => 'info',
          'hint' => $storage['public_url'],
        ],
      ],
      'migrationsExpandedDefault' => $pendingCount > 0,
      'seederGroups' => app(ProductionSeederService::class)->catalog(),
      'seederConfirms' => collect(app(ProductionSeederService::class)->all())
        ->mapWithKeys(fn (array $item, string $key): array => [$key => $item['confirm'] ?? 'Exécuter ce seeder ?'])
        ->all(),
    ];
  }

  /**
   * @param  Notification  $notification  Notification Filament
   */
  protected function sendDeploymentFeedback(Notification $notification): void
  {
    $this->sendFilamentFeedback($notification);
  }
}
