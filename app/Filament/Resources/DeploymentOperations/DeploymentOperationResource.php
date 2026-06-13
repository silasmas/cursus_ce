<?php

namespace App\Filament\Resources\DeploymentOperations;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\DeploymentOperations\Pages\ListDeploymentOperations;
use App\Filament\Resources\DeploymentOperations\Pages\ViewDeploymentOperation;
use App\Filament\Resources\DeploymentOperations\Schemas\DeploymentOperationInfolist;
use App\Filament\Resources\DeploymentOperations\Tables\DeploymentOperationsTable;
use App\Models\DeploymentOperation;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Maintenance production : migrations, permissions Shield et lien storage public.
 */
class DeploymentOperationResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = DeploymentOperation::class;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

  protected static string|\UnitEnum|null $navigationGroup = 'Système';

  protected static ?int $navigationSort = 0;

  protected static ?string $slug = 'maintenance-production';

  protected static ?string $recordTitleAttribute = 'command';

  /**
   * Libellé singulier.
   */
  public static function getModelLabel(): string
  {
    return 'Opération de maintenance';
  }

  /**
   * Libellé pluriel.
   */
  public static function getPluralModelLabel(): string
  {
    return 'Maintenance production';
  }

  /**
   * Entrée de menu latéral.
   */
  public static function getNavigationLabel(): string
  {
    return 'Maintenance production';
  }

  /**
   * Réservé aux super administrateurs (opérations sensibles).
   */
  public static function canViewAny(): bool
  {
    $user = Auth::guard('admin')->user();

    return $user instanceof User
      && $user->hasRole(config('filament-shield.super_admin.name', 'super_admin'), 'admin');
  }

  /**
   * Les opérations sont créées automatiquement, jamais à la main.
   */
  public static function canCreate(): bool
  {
    return false;
  }

  /**
   * Journal en lecture seule.
   */
  public static function canEdit($record): bool
  {
    return false;
  }

  /**
   * Journal en lecture seule.
   */
  public static function canDelete($record): bool
  {
    return false;
  }

  public static function infolist(Schema $schema): Schema
  {
    return DeploymentOperationInfolist::configure($schema);
  }

  public static function table(Table $table): Table
  {
    return DeploymentOperationsTable::configure($table);
  }

  public static function getRelations(): array
  {
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => ListDeploymentOperations::route('/'),
      'view' => ViewDeploymentOperation::route('/{record}'),
    ];
  }
}
