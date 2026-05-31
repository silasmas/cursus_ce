<?php

namespace App\Filament\Resources\AcademicSessions\RelationManagers;

use App\Enums\PeriodContentType;
use App\Enums\SessionPeriodType;
use App\Filament\Concerns\HasRelationManagerHelp;
use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\CourseModule;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Périodes ECAP (cours, TFE, défenses) et affectation des contenus.
 */
class SessionPeriodsRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  protected static string $relationship = 'sessionPeriods';

  protected static ?string $title = 'Périodes pédagogiques — cours, TFE, défenses';

  protected static ?string $modelLabel = 'période';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'session_periods';
  }

  /**
   * Formulaire d'une période et de ses contenus.
   */
  public function form(Schema $schema): Schema
  {
    $help = config('filament_field_help.period');
    $sortHelp = config('filament_field_help.sort_order');

    return $schema
      ->components([
        Select::make('type')
          ->label('Type de période')
          ->options(collect(SessionPeriodType::cases())->mapWithKeys(
            fn (SessionPeriodType $type) => [$type->value => $type->label()],
          )->all())
          ->required()
          ->native(false)
          ->helperText($help['type']),
        TextInput::make('name')
          ->label('Nom affiché (optionnel)')
          ->maxLength(150)
          ->placeholder('Ex. Période des cours — 1er trimestre')
          ->helperText($help['name']),
        DatePicker::make('starts_on')
          ->label('Début')
          ->required()
          ->helperText($help['starts_on']),
        DatePicker::make('ends_on')
          ->label('Fin')
          ->required()
          ->afterOrEqual('starts_on')
          ->helperText($help['ends_on']),
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
        Repeater::make('contents')
          ->label('Contenus affectés')
          ->relationship()
          ->schema([
            Select::make('content_type')
              ->label('Type de contenu')
              ->options(collect(PeriodContentType::cases())->mapWithKeys(
                fn (PeriodContentType $type) => [$type->value => $type->label()],
              )->all())
              ->required()
              ->live()
              ->native(false)
              ->helperText($help['content_type']),
            Select::make('content_id')
              ->label('Contenu')
              ->options(fn (Get $get): array => $this->contentOptions($get('content_type')))
              ->searchable()
              ->required()
              ->helperText($help['content_id']),
            TextInput::make('label')
              ->label('Libellé (optionnel)')
              ->maxLength(200)
              ->helperText($help['content_label']),
            TextInput::make('sort_order')
              ->label('Ordre')
              ->numeric()
              ->default(0)
              ->helperText($sortHelp),
          ])
          ->collapsible()
          ->itemLabel(fn (array $state): ?string => $state['label'] ?? PeriodContentType::tryFrom($state['content_type'] ?? '')?->label())
          ->defaultItems(0)
          ->addActionLabel('Ajouter un contenu'),
      ]);
  }

  /**
   * Liste des périodes de la génération.
   */
  public function table(Table $table): Table
  {
    return $table
      ->defaultSort('sort_order')
      ->columns([
        TextColumn::make('type')
          ->label('Type')
          ->formatStateUsing(fn ($state) => $state instanceof SessionPeriodType ? $state->label() : $state)
          ->badge(),
        TextColumn::make('period_label')
          ->label('Période')
          ->getStateUsing(fn ($record) => $record->display_label),
        TextColumn::make('starts_on')
          ->label('Début')
          ->date('d/m/Y')
          ->sortable(),
        TextColumn::make('ends_on')
          ->label('Fin')
          ->date('d/m/Y')
          ->sortable(),
        TextColumn::make('contents_count')
          ->label('Contenus')
          ->counts('contents'),
        IconColumn::make('is_active_now')
          ->label('En cours')
          ->boolean()
          ->state(fn ($record) => $record->isActiveNow()),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->sortable(),
      ])
      ->headerActions([
        CreateAction::make()->label('Ajouter une période'),
      ])
      ->recordActions([
        EditAction::make(),
        DeleteAction::make(),
      ]);
  }

  /**
   * Options de contenu selon le type choisi.
   *
   * @return array<int, string>
   */
  private function contentOptions(?string $contentType): array
  {
    $type = PeriodContentType::tryFrom($contentType ?? '');

    if ($type === null) {
      return [];
    }

    $programId = $this->getOwnerRecord()->program_id;

    return match ($type) {
      PeriodContentType::CourseModule => CourseModule::query()
        ->whereHas('course', fn ($query) => $query->where('program_id', $programId))
        ->with('course')
        ->orderBy('sort_order')
        ->get()
        ->mapWithKeys(fn (CourseModule $module) => [
          $module->id => trim(($module->course?->name ?? '').' — '.$module->name, ' —'),
        ])
        ->all(),
      PeriodContentType::Chapter => Chapter::query()
        ->whereHas('course', fn ($query) => $query->where('program_id', $programId))
        ->with(['course', 'courseModule'])
        ->orderBy('sort_order')
        ->get()
        ->mapWithKeys(fn (Chapter $chapter) => [
          $chapter->id => trim(($chapter->courseModule?->name ?? $chapter->course?->name ?? '').' — '.$chapter->title, ' —'),
        ])
        ->all(),
      PeriodContentType::Assessment => Assessment::query()
        ->where('program_id', $programId)
        ->orderBy('title')
        ->pluck('title', 'id')
        ->all(),
    };
  }
}
