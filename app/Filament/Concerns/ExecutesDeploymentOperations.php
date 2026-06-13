<?php

namespace App\Filament\Concerns;

use App\Enums\DeploymentOperationStatus;
use App\Services\System\ProductionSeederService;
use App\Services\System\SystemDeploymentService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

/**
 * Actions de maintenance production réutilisables (widget Filament).
 */
trait ExecutesDeploymentOperations
{
  /**
   * Actualise le diagnostic des migrations dans le journal.
   */
  public function refreshMigrationStatus(): void
  {
    app(SystemDeploymentService::class)->recordMigrationStatus(Auth::user());

    $this->sendDeploymentFeedback(
      Notification::make()
        ->title('État des migrations actualisé')
        ->success(),
    );
  }

  /**
   * Exécute les migrations en attente (--force).
   */
  public function runMigrations(): void
  {
    $this->executeDeploymentOperation(
      fn (SystemDeploymentService $service) => $service->runMigrations(Auth::user()),
      'Migrations exécutées',
    );
  }

  /**
   * Régénère les permissions Filament Shield.
   */
  public function runShieldGenerate(): void
  {
    $this->executeDeploymentOperation(
      fn (SystemDeploymentService $service) => $service->runShieldGenerate(Auth::user()),
      'Permissions Shield générées',
    );
  }

  /**
   * Crée le dossier storage/app/public.
   */
  public function prepareStorageDirectory(): void
  {
    $this->executeDeploymentOperation(
      fn (SystemDeploymentService $service) => $service->prepareStorageDirectory(Auth::user()),
      'Dossier storage préparé',
    );
  }

  /**
   * Crée le lien symbolique public/storage.
   */
  public function runStorageLink(): void
  {
    $this->executeDeploymentOperation(
      fn (SystemDeploymentService $service) => $service->runStorageLink(Auth::user()),
      'Lien storage créé',
    );
  }

  /**
   * Prépare le dossier et le lien public/storage.
   */
  public function setupPublicStorage(): void
  {
    $service = app(SystemDeploymentService::class);
    $results = $service->setupPublicStorage(Auth::user());
    $failed = collect($results)->contains(
      fn ($operation) => $operation->status === DeploymentOperationStatus::Failed,
    );

    $this->sendDeploymentFeedback(
      Notification::make()
        ->title($failed ? 'Préparation partielle ou échouée' : 'Stockage public prêt')
        ->color($failed ? 'warning' : 'success')
        ->body($failed
          ? 'Consultez le journal ci-dessous pour le détail.'
          : 'Le dossier et le lien symbolique sont configurés.'),
    );
  }

  /**
   * Exécute un seeder de démarrage production (config/production_seeders.php).
   *
   * @param  string  $seederKey  Identifiant du seeder
   */
  public function runProductionSeeder(string $seederKey): void
  {
    try {
      $operation = app(ProductionSeederService::class)->run($seederKey, Auth::user());
      $failed = $operation->status === DeploymentOperationStatus::Failed;
      $label = $operation->parameters['label'] ?? $seederKey;

      $this->sendDeploymentFeedback(
        Notification::make()
          ->title($failed ? 'Seeder échoué' : 'Seeder exécuté')
          ->color($failed ? 'danger' : 'success')
          ->body($failed
            ? '« '.$label.' » — consultez le journal pour le détail.'
            : '« '.$label.' » a été appliqué avec succès.'),
      );
    } catch (\Throwable $exception) {
      report($exception);

      $this->sendDeploymentFeedback(
        Notification::make()
          ->title('Seeder impossible')
          ->danger()
          ->body($exception->getMessage()),
      );
    }
  }

  /**
   * Exécute une opération et notifie l'utilisateur.
   *
   * @param  callable(SystemDeploymentService): \App\Models\DeploymentOperation  $callback  Opération à lancer
   * @param  string  $successTitle  Titre en cas de succès
   */
  protected function executeDeploymentOperation(callable $callback, string $successTitle): void
  {
    $operation = $callback(app(SystemDeploymentService::class));
    $failed = $operation->status === DeploymentOperationStatus::Failed;

    $this->sendDeploymentFeedback(
      Notification::make()
        ->title($failed ? 'Opération échouée' : $successTitle)
        ->color($failed ? 'danger' : 'success')
        ->body($failed
          ? 'Consultez le journal ou ouvrez la fiche détail pour la sortie console.'
          : 'L\'opération a été enregistrée dans le journal.'),
    );
  }

  /**
   * Envoie une notification Filament depuis le widget ou la page.
   *
   * @param  Notification  $notification  Notification configurée
   */
  abstract protected function sendDeploymentFeedback(Notification $notification): void;
}
