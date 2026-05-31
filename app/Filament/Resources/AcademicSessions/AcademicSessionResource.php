<?php

namespace App\Filament\Resources\AcademicSessions;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\AcademicSessions\Pages\CreateAcademicSession;
use App\Filament\Resources\AcademicSessions\Pages\EditAcademicSession;
use App\Filament\Resources\AcademicSessions\Pages\ListAcademicSessions;
use App\Filament\Resources\AcademicSessions\RelationManagers\LearningGroupsRelationManager;
use App\Filament\Resources\AcademicSessions\RelationManagers\ModuleSchedulesRelationManager;
use App\Filament\Resources\AcademicSessions\RelationManagers\SessionPeriodsRelationManager;
use App\Filament\Resources\AcademicSessions\RelationManagers\SessionVacationsRelationManager;
use App\Filament\Resources\AcademicSessions\Schemas\AcademicSessionForm;
use App\Filament\Resources\AcademicSessions\Tables\AcademicSessionsTable;
use App\Models\AcademicSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sessions ECAP — calendrier et vacations présentiel.
 */
class AcademicSessionResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = AcademicSession::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

  protected static string|\UnitEnum|null $navigationGroup = 'ECAP';

  protected static ?int $navigationSort = 1;

  protected static ?string $slug = 'sessions-ecap';

  protected static ?string $recordTitleAttribute = 'name';

  /**
   * Libellé singulier (titres, fil d'Ariane).
   */
  public static function getModelLabel(): string
  {
    return 'Session ECAP';
  }

  /**
   * Libellé pluriel (liste).
   */
  public static function getPluralModelLabel(): string
  {
    return 'Sessions ECAP';
  }

  /**
   * Entrée unique du menu latéral.
   */
  public static function getNavigationLabel(): string
  {
    return 'Sessions & calendrier';
  }

  /**
   * Uniquement les sessions du cursus ECAP.
   */
  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->whereHas('program', fn (Builder $query) => $query->where('slug', 'ecap'));
  }

  public static function form(Schema $schema): Schema
  {
    return AcademicSessionForm::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return AcademicSessionsTable::configure($table);
  }

  public static function getRelations(): array
  {
    return [
      ModuleSchedulesRelationManager::class,
      SessionPeriodsRelationManager::class,
      SessionVacationsRelationManager::class,
      LearningGroupsRelationManager::class,
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListAcademicSessions::route('/'),
      'create' => CreateAcademicSession::route('/create'),
      'edit' => EditAcademicSession::route('/{record}/edit'),
    ];
  }
}
