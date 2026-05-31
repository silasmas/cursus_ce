<?php

namespace App\Filament\Resources\Chapters\Tables;

use App\Models\Chapter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tableau des chapitres, regroupés par module de cours.
 */
class ChaptersTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultGroup(
        Group::make('courseModule.name')
          ->label('Module de cours')
          ->collapsible()
          ->titlePrefixedWithLabel(false)
          ->getTitleFromRecordUsing(
            fn (Chapter $record): string => $record->courseModule?->name ?? 'Sans module',
          )
          ->getDescriptionFromRecordUsing(
            fn (Chapter $record): ?string => $record->course?->name
              ? 'Cours : '.$record->course->name
              : null,
          )
          ->orderQueryUsing(
            fn (Builder $query, string $direction): Builder => $query
              ->leftJoin('course_modules', 'course_modules.id', '=', 'chapters.course_module_id')
              ->orderBy('course_modules.name', $direction)
              ->orderBy('chapters.sort_order')
              ->select('chapters.*'),
          ),
      )
      ->groupingSettingsHidden()
      ->groupingDirectionSettingHidden()
      ->defaultSort('sort_order')
      ->columns([
        TextColumn::make('course.name')
          ->label('Cours')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('courseModule.name')
          ->label('Module')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('title')
          ->label('Titre')
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
        IconColumn::make('is_published')
          ->label('Publié')
          ->boolean(),
        TextColumn::make('created_at')
          ->label('Créé le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->label('Modifié le')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->recordActions([
        EditAction::make()->label('Modifier'),
        DeleteAction::make()->label('Supprimer'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()->label('Supprimer la sélection'),
        ]),
      ]);
  }
}
