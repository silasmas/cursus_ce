<?php

namespace App\Filament\Resources\MentorSettings;

use App\Filament\Concerns\HasFrenchFilamentLabels;
use App\Filament\Resources\MentorSettings\Pages\ManageMentorSettings;
use App\Filament\Resources\MentorSettings\Schemas\MentorSettingForm;
use App\Filament\Resources\MentorSettings\Tables\MentorSettingsTable;
use App\Models\MentorSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Paramètres globaux du module mentorat.
 */
class MentorSettingResource extends Resource
{
  use HasFrenchFilamentLabels;

  protected static ?string $model = MentorSetting::class;

  protected static ?string $navigationLabel = 'Paramètres mentorat';

  protected static ?string $modelLabel = 'paramètre mentorat';

  protected static ?string $pluralModelLabel = 'Paramètres mentorat';

  protected static bool $hasTitleCaseModelLabel = false;

  protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

  protected static string|\UnitEnum|null $navigationGroup = 'Mentorat';

  protected static ?int $navigationSort = 5;

  protected static ?string $slug = 'parametres-mentorat';

  /**
   * Schéma du formulaire.
   */
  public static function form(Schema $schema): Schema
  {
    return MentorSettingForm::configure($schema);
  }

  /**
   * Tableau des enregistrements.
   */
  public static function table(Table $table): Table
  {
    return MentorSettingsTable::configure($table);
  }

  /**
   * Pages de la ressource.
   *
   * @return array<string, \Filament\Resources\Pages\PageRegistration>
   */
  public static function getPages(): array
  {
    return [
      'index' => ManageMentorSettings::route('/'),
    ];
  }

  /**
   * Configuration globale unique — pas de création manuelle.
   */
  public static function canCreate(): bool
  {
    return false;
  }
}

