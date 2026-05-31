<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Services\Portal\PortalMailService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * Notifications Filament pour les administrateurs.
 */
class AdminNotificationService
{
  /**
   * @param  PortalMailService  $mailService  Envoi e-mail aux admins
   */
  public function __construct(
    private readonly PortalMailService $mailService,
  ) {}

  /**
   * Utilisateurs pouvant accéder au panel admin.
   *
   * @return Collection<int, User>
   */
  public function adminUsers(): Collection
  {
    return User::query()
      ->whereHas('roles', fn ($q) => $q->where('guard_name', 'admin'))
      ->get();
  }

  /**
   * Envoie une notification base de données à tous les admins.
   */
  public function notifyAdmins(string $title, string $body, ?string $url = null): void
  {
    foreach ($this->adminUsers() as $admin) {
      $notification = Notification::make()
        ->title($title)
        ->body($body)
        ->icon('heroicon-o-clipboard-document-check')
        ->warning();

      if ($url) {
        $notification->actions([
          Action::make('view')
            ->label('Voir')
            ->url($url),
        ]);
      }

      $admin->notifyNow($notification->toDatabase());

      $this->mailService->sendToUser(
        $admin,
        $title.' — PHILA-CE',
        $title,
        $body,
        $url,
        'Voir dans l\'administration',
      );
    }
  }

  /**
   * Nombre de TP mentor en attente de validation admin.
   */
  public function pendingMentorTpCount(): int
  {
    return \App\Models\AssignmentSubmission::query()
      ->whereNotNull('submitted_by_user_id')
      ->where('admin_publication_status', 'pending_review')
      ->count();
  }
}
