<?php

namespace App\Filament\Resources\AcademicSessions\RelationManagers;

use App\Filament\Concerns\HasRelationManagerHelp;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Vacations présentiel proposées à l'inscription ECAP.
 */
class SessionVacationsRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  protected static string $relationship = 'sessionVacations';

  protected static ?string $title = 'Vacations (présentiel)';

  protected static ?string $modelLabel = 'vacation';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'session_vacations';
  }

  /**
   * Formulaire d'une vacation.
   */
  public function form(Schema $schema): Schema
  {
    $help = config('filament_field_help.vacation');
    $sortHelp = config('filament_field_help.sort_order');

    return $schema
      ->components([
        TextInput::make('name')
          ->label('Nom')
          ->required()
          ->maxLength(150)
          ->helperText($help['name']),
        TextInput::make('code')
          ->label('Code')
          ->maxLength(30)
          ->helperText($help['code']),
        TimePicker::make('time_starts')
          ->label('Heure de début')
          ->seconds(false)
          ->required()
          ->helperText($help['time_starts']),
        TimePicker::make('time_ends')
          ->label('Heure de fin')
          ->seconds(false)
          ->required()
          ->after('time_starts')
          ->helperText($help['time_ends']),
        TextInput::make('capacity_max')
          ->label('Capacité max.')
          ->numeric()
          ->minValue(1)
          ->helperText($help['capacity_max']),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->default(0)
          ->required()
          ->helperText($sortHelp),
        Toggle::make('is_active')
          ->label('Active')
          ->default(true)
          ->helperText($help['is_active']),
      ]);
  }

  /**
   * Liste des vacations de la session.
   */
  public function table(Table $table): Table
  {
    return $table
      ->defaultSort('sort_order')
      ->columns([
        TextColumn::make('name')
          ->label('Vacation')
          ->searchable(),
        TextColumn::make('time_range')
          ->label('Tranche horaire')
          ->state(fn ($record) => $record->timeRangeLabel() ?? '—'),
        TextColumn::make('code')
          ->label('Code')
          ->toggleable(),
        TextColumn::make('capacity_max')
          ->label('Capacité')
          ->toggleable(),
        IconColumn::make('is_active')
          ->label('Active')
          ->boolean(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->sortable(),
      ])
      ->headerActions([
        CreateAction::make()->label('Ajouter une vacation'),
      ])
      ->recordActions([
        EditAction::make(),
        DeleteAction::make(),
      ]);
  }
}
