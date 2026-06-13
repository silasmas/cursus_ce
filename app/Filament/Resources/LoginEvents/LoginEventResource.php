<?php

namespace App\Filament\Resources\LoginEvents;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\LoginEvents\Pages\ListLoginEvents;
use App\Filament\Resources\LoginEvents\Tables\LoginEventsTable;
use App\Models\LoginEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Historique des connexions et statistiques appareils.
 */
class LoginEventResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = LoginEvent::class;

  protected static ?string $navigationLabel = 'Connexions & appareils';

  protected static ?string $modelLabel = 'connexion';

  protected static ?string $pluralModelLabel = 'Connexions';

  protected static bool $hasTitleCaseModelLabel = false;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

  protected static string|\UnitEnum|null $navigationGroup = 'Administration';

  protected static ?int $navigationSort = 15;

  protected static ?string $slug = 'connexions-appareils';

  /**
   * Lecture seule — pas de formulaire d'édition.
   */
  public static function form(Schema $schema): Schema
  {
    return $schema->components([]);
  }

  public static function table(Table $table): Table
  {
    return LoginEventsTable::configure($table);
  }

  public static function getPages(): array
  {
    return [
      'index' => ListLoginEvents::route('/'),
    ];
  }

  public static function canCreate(): bool
  {
    return false;
  }
}
