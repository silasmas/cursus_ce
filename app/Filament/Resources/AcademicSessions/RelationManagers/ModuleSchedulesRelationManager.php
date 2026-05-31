<?php

namespace App\Filament\Resources\AcademicSessions\RelationManagers;

use App\Enums\CalendarItemType;
use App\Filament\Concerns\HasRelationManagerHelp;
use App\Models\CourseModule;
use App\Models\SessionPeriod;
use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

/**
 * Calendrier ECAP : modules de cours et activités rattachées à une période.
 */
class ModuleSchedulesRelationManager extends RelationManager
{
  use HasRelationManagerHelp;

  protected static string $relationship = 'moduleSchedules';

  protected static ?string $title = 'Calendrier (modules & activités)';

  protected static ?string $modelLabel = 'entrée calendrier';

  /**
   * Clé d'aide contextuelle.
   */
  protected static function helpKey(): string
  {
    return 'module_schedules';
  }

  /**
   * Formulaire d'une entrée calendrier.
   */
  public function form(Schema $schema): Schema
  {
    $help = config('filament_field_help');

    return $schema
      ->components([
        Select::make('item_type')
          ->label('Type')
          ->options(collect(CalendarItemType::cases())->mapWithKeys(
            fn (CalendarItemType $type) => [$type->value => $type->label()],
          )->all())
          ->default(CalendarItemType::Module->value)
          ->required()
          ->live()
          ->native(false)
          ->helperText($help['calendar']['item_type']),
        Select::make('session_period_id')
          ->label('Période pédagogique')
          ->options(fn (): array => $this->periodOptions())
          ->searchable()
          ->helperText($help['calendar']['session_period_id']),
        Select::make('course_module_id')
          ->label('Module')
          ->options(fn (): array => $this->moduleOptions())
          ->searchable()
          ->required(fn (Get $get): bool => $get('item_type') === CalendarItemType::Module->value)
          ->visible(fn (Get $get): bool => $get('item_type') === CalendarItemType::Module->value)
          ->disabledOn('edit')
          ->helperText($help['calendar']['course_module_id']),
        TextInput::make('title')
          ->label('Titre de l\'activité')
          ->maxLength(200)
          ->required(fn (Get $get): bool => $get('item_type') === CalendarItemType::Activity->value)
          ->visible(fn (Get $get): bool => $get('item_type') === CalendarItemType::Activity->value)
          ->helperText($help['calendar']['title']),
        Textarea::make('description')
          ->label('Description')
          ->rows(2)
          ->visible(fn (Get $get): bool => $get('item_type') === CalendarItemType::Activity->value)
          ->helperText($help['calendar']['description']),
        DatePicker::make('starts_on')
          ->label('Début')
          ->required()
          ->helperText($help['calendar']['starts_on']),
        DatePicker::make('ends_on')
          ->label('Fin')
          ->required()
          ->afterOrEqual('starts_on')
          ->helperText($help['calendar']['ends_on']),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->default(0)
          ->required()
          ->helperText($help['sort_order']),
      ]);
  }

  /**
   * Table du calendrier.
   */
  public function table(Table $table): Table
  {
    return $table
      ->defaultSort('starts_on', 'desc')
      ->columns([
        TimelineEntry::make()
          ->title(fn ($record) => $record->displayLabel())
          ->content(fn ($record) => trim(
            ($record->item_type instanceof CalendarItemType ? $record->item_type->label() : 'Entrée')
            .($record->sessionPeriod?->display_label ? ' · '.$record->sessionPeriod->display_label : '')
            ."\nDu ".$record->starts_on?->format('d/m/Y').' au '.$record->ends_on?->format('d/m/Y')
          ))
          ->time('starts_on', 'd/m/Y'),
      ])
      ->defaultGroup(
        Group::make('starts_on')
          ->date()
          ->collapsible()
          ->orderQueryUsing(fn ($query) => $query->orderByDesc('starts_on'))
      )
      ->emptyStateHeading('Calendrier vide')
      ->emptyStateDescription('Ajoutez des modules ou activités pour alimenter la timeline ECAP côté fidèle.')
      ->paginated([10])
      ->headerActions([
        CreateAction::make()
          ->label('Ajouter au calendrier')
          ->successNotification(
            Notification::make()
              ->title('Calendrier enregistré')
              ->success()
              ->body('Cette entrée apparaîtra sur la timeline des fidèles (Mon espace → Calendrier ECAP).'),
          ),
      ])
      ->recordActions([
        ActionGroup::make([
          EditAction::make(),
          DeleteAction::make(),
        ]),
      ])
      ->asTimeline();
  }

  /**
   * Périodes configurées pour cette génération.
   *
   * @return array<int, string>
   */
  private function periodOptions(): array
  {
    return SessionPeriod::query()
      ->where('academic_session_id', $this->getOwnerRecord()->id)
      ->where('is_active', true)
      ->orderBy('sort_order')
      ->get()
      ->mapWithKeys(fn (SessionPeriod $period) => [
        $period->id => $period->display_label,
      ])
      ->all();
  }

  /**
   * Modules du programme ECAP de la session.
   *
   * @return array<int, string>
   */
  private function moduleOptions(): array
  {
    $session = $this->getOwnerRecord();
    $programId = $session->program_id;

    return CourseModule::query()
      ->whereHas('course', fn ($query) => $query->where('program_id', $programId))
      ->with('course')
      ->orderBy('sort_order')
      ->get()
      ->mapWithKeys(fn (CourseModule $module) => [
        $module->id => trim(($module->course?->name ?? '').' — '.$module->name, ' —'),
      ])
      ->all();
  }
}
