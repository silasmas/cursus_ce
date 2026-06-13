<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Resources\LoginEvents\LoginEventResource;
use App\Filament\Resources\ProgramAccesses\ProgramAccessResource;
use App\Filament\Resources\VacationQuestions\VacationQuestionResource;
use App\Services\Analytics\AdminDashboardStatisticsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Indicateurs globaux PHILA-CE en tête du tableau de bord admin.
 */
class AdminGlobalStatsOverview extends StatsOverviewWidget
{
  protected static ?int $sort = 1;

  protected ?string $heading = 'Vue d\'ensemble';

  protected ?string $description = 'Chiffres clés de la plateforme — cliquez sur une carte pour ouvrir la rubrique correspondante.';

  protected int | string | array $columnSpan = 'full';

  /**
   * @return array<int, Stat>
   */
  protected function getStats(): array
  {
    $stats = app(AdminDashboardStatisticsService::class)->globalStats();

    return [
      Stat::make('Comptes utilisateurs', (string) $stats['members'])
        ->description('Tous profils confondus')
        ->descriptionIcon('heroicon-m-users')
        ->color('gray'),
      Stat::make('Inscriptions actives', (string) $stats['active_enrollments'])
        ->description('Parcours en cours')
        ->descriptionIcon('heroicon-m-academic-cap')
        ->color('info')
        ->url(EnrollmentResource::getUrl('index')),
      Stat::make('Certificats '.now()->year, (string) $stats['certificates_year'])
        ->description('Délivrés depuis janvier')
        ->descriptionIcon('heroicon-m-document-check')
        ->color('success')
        ->url(CertificateResource::getUrl('index')),
      Stat::make('Accès à valider', (string) $stats['pending_validations'])
        ->description('Déclarations « déjà suivi »')
        ->descriptionIcon('heroicon-m-clock')
        ->color($stats['pending_validations'] > 0 ? 'warning' : 'gray')
        ->url(ProgramAccessResource::getUrl('index')),
      Stat::make('Questions ECAP en attente', (string) $stats['open_vacation_questions'])
        ->description('Fil Q&R sans réponse')
        ->descriptionIcon('heroicon-m-chat-bubble-left-right')
        ->color($stats['open_vacation_questions'] > 0 ? 'warning' : 'gray')
        ->url(VacationQuestionResource::getUrl('index')),
      Stat::make('TP à corriger', (string) $stats['tp_pending_grading'])
        ->description('Remises non notées')
        ->descriptionIcon('heroicon-m-clipboard-document-check')
        ->color($stats['tp_pending_grading'] > 0 ? 'danger' : 'gray'),
      Stat::make('Connexions (30 j.)', (string) $stats['logins_30_days'])
        ->description('Portail fidèle')
        ->descriptionIcon('heroicon-m-signal')
        ->color('primary')
        ->url(LoginEventResource::getUrl('index')),
      Stat::make('Sessions ECAP actives', (string) $stats['active_ecap_sessions'])
        ->description('Générations ouvertes')
        ->descriptionIcon('heroicon-m-calendar-days')
        ->color('primary'),
    ];
  }
}
