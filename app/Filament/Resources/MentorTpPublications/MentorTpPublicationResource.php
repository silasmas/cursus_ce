<?php

namespace App\Filament\Resources\MentorTpPublications;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\MentorTpPublications\Pages\ListMentorTpPublications;
use App\Filament\Resources\MentorTpPublications\Pages\ViewMentorTpPublication;
use App\Filament\Resources\MentorTpPublications\Schemas\MentorTpPublicationInfolist;
use App\Filament\Resources\MentorTpPublications\Tables\MentorTpPublicationsTable;
use App\Models\AssignmentSubmission;
use App\Services\Admin\AdminNotificationService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Remises de TP effectuées par les mentors — validation admin avant publication mentoré.
 */
class MentorTpPublicationResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = AssignmentSubmission::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

  protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

  protected static ?int $navigationSort = 1;

  protected static ?string $slug = 'tp-mentors';

  protected static ?string $recordTitleAttribute = 'id';

  /**
   * Libellé navigation (config + fallback).
   */
  public static function getNavigationLabel(): string
  {
    return static::frenchLabelConfig()['navigation'] ?? 'TP mentors à valider';
  }

  /**
   * Badge : nombre de TP en attente.
   */
  public static function getNavigationBadge(): ?string
  {
    $count = app(AdminNotificationService::class)->pendingMentorTpCount();

    return $count > 0 ? (string) $count : null;
  }

  /**
   * Couleur du badge navigation.
   */
  public static function getNavigationBadgeColor(): ?string
  {
    return 'warning';
  }

  /**
   * Uniquement les remises mentor en attente ou récemment traitées.
   */
  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->whereNotNull('submitted_by_user_id')
      ->with(['assessment', 'user', 'submittedBy']);
  }

  public static function table(Table $table): Table
  {
    return MentorTpPublicationsTable::configure($table);
  }

  /**
   * Schéma infolist pour la page de détail.
   */
  public static function infolist(Schema $schema): Schema
  {
    return MentorTpPublicationInfolist::configure($schema);
  }

  public static function getPages(): array
  {
    return [
      'index' => ListMentorTpPublications::route('/'),
      'view' => ViewMentorTpPublication::route('/{record}'),
    ];
  }

  public static function canCreate(): bool
  {
    return false;
  }
}
