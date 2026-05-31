<?php

namespace App\Filament\Resources\CourseModules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tableau des modules de cours, regroupés par cursus.
 */
class CourseModulesTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultGroup(
        Group::make('course.name')
          ->label('Cours')
          ->collapsible()
          ->titlePrefixedWithLabel(false)
          ->orderQueryUsing(
            fn (Builder $query, string $direction): Builder => $query
              ->join('courses', 'courses.id', '=', 'course_modules.course_id')
              ->orderBy('courses.name', $direction)
              ->orderBy('course_modules.sort_order')
              ->select('course_modules.*'),
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
        TextColumn::make('name')
          ->label('Nom du module')
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
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
