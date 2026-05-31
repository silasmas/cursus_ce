<?php

namespace App\Filament\Resources\ContentBlocks\Tables;

use App\Models\ContentBlock;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tableau du contenu des chapitres, regroupé par chapitre.
 */
class ContentBlocksTable
{
  /**
   * Libellés français des types de blocs.
   *
   * @var array<string, string>
   */
  private const TYPE_LABELS = [
    'text' => 'Texte',
    'video' => 'Vidéo',
    'audio' => 'Audio',
    'file' => 'Fichier',
    'image' => 'Image',
  ];

  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultGroup(
        Group::make('chapter.title')
          ->label('Chapitre')
          ->collapsible()
          ->titlePrefixedWithLabel(false)
          ->getTitleFromRecordUsing(
            fn (ContentBlock $record): string => $record->chapter?->title ?? 'Sans chapitre',
          )
          ->getDescriptionFromRecordUsing(function (ContentBlock $record): ?string {
            $module = $record->chapter?->courseModule?->name;
            $course = $record->chapter?->course?->name;

            if ($module && $course) {
              return $course.' — '.$module;
            }

            return $course ?? $module;
          })
          ->orderQueryUsing(
            fn (Builder $query, string $direction): Builder => $query
              ->join('chapters', 'chapters.id', '=', 'content_blocks.chapter_id')
              ->orderBy('chapters.title', $direction)
              ->orderBy('content_blocks.sort_order')
              ->select('content_blocks.*'),
          ),
      )
      ->groupingSettingsHidden()
      ->groupingDirectionSettingHidden()
      ->defaultSort('sort_order')
      ->columns([
        TextColumn::make('chapter.title')
          ->label('Chapitre')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('type')
          ->label('Type')
          ->formatStateUsing(fn (string $state): string => self::TYPE_LABELS[$state] ?? $state)
          ->searchable(),
        TextColumn::make('title')
          ->label('Titre')
          ->searchable(),
        TextColumn::make('sort_order')
          ->label('Ordre')
          ->numeric()
          ->sortable(),
        TextColumn::make('mediaAsset.path')
          ->label('Média')
          ->searchable()
          ->toggleable(),
        TextColumn::make('url')
          ->label('URL externe')
          ->searchable()
          ->toggleable(),
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
