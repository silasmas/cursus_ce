<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Services\Content\ContentBlockMediaService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Blocs de contenu d'un chapitre.
 */
class ContentBlocksRelationManager extends RelationManager
{
  protected static string $relationship = 'contentBlocks';

  protected static ?string $title = 'Contenu du chapitre';

  protected static ?string $modelLabel = 'contenu de chapitre';

  /**
   * Types de blocs (libellés français).
   *
   * @return array<string, string>
   */
  private function typeOptions(): array
  {
    return [
      'text' => 'Texte',
      'video' => 'Vidéo',
      'audio' => 'Audio',
      'file' => 'Fichier',
      'image' => 'Image',
    ];
  }

  /**
   * Formulaire d'un bloc.
   */
  public function form(Schema $schema): Schema
  {
    $help = config('filament_field_help.content_block');

    return $schema
      ->components([
        Select::make('type')
          ->label('Type')
          ->options($this->typeOptions())
          ->required()
          ->native(false)
          ->helperText($help['type']),
        TextInput::make('sort_order')
          ->label('Ordre')
          ->required()
          ->numeric()
          ->default(0)
          ->helperText($help['sort_order']),
        TextInput::make('title')
          ->label('Titre')
          ->helperText($help['title']),
        Textarea::make('body')
          ->label('Contenu texte')
          ->columnSpanFull()
          ->helperText($help['body']),
        Select::make('media_asset_id')
          ->label('Média')
          ->relationship('mediaAsset', 'path')
          ->searchable()
          ->preload()
          ->helperText($help['media_asset_id']),
        TextInput::make('url')
          ->label('URL YouTube (référence)')
          ->url()
          ->placeholder('https://www.youtube.com/watch?v=…')
          ->visible(fn ($get): bool => $get('type') === 'video')
          ->helperText($help['url']),
        FileUpload::make('video_upload')
          ->label('Fichier vidéo MP4')
          ->disk('public')
          ->directory('course-videos/uploads')
          ->acceptedFileTypes(['video/mp4', 'video/webm'])
          ->maxSize(512000)
          ->visible(fn ($get): bool => $get('type') === 'video')
          ->helperText('Recommandé : uploadez le MP4 exporté depuis YouTube Studio pour une lecture fiable sur la plateforme.')
          ->columnSpanFull(),
        Textarea::make('metadata')
          ->label('Métadonnées (JSON)')
          ->columnSpanFull()
          ->helperText($help['metadata']),
      ]);
  }

  /**
   * Liste des blocs du chapitre.
   */
  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('title')
      ->columns([
        TextColumn::make('type')
          ->label('Type')
          ->formatStateUsing(fn (string $state): string => $this->typeOptions()[$state] ?? $state)
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
          ->toggleable(),
        TextColumn::make('url')
          ->label('URL')
          ->toggleable(),
      ])
      ->headerActions([
        CreateAction::make()
          ->label('Ajouter un contenu')
          ->mutateFormDataUsing(function (array $data): array {
            unset($data['video_upload']);

            return $data;
          })
          ->after(function ($record, array $data): void {
            $upload = $data['video_upload'] ?? null;
            $path = is_array($upload) ? ($upload[0] ?? null) : $upload;

            if (is_string($path) && $path !== '' && $record->type === 'video') {
              app(ContentBlockMediaService::class)->attachStoredVideo($record, $path);
            }
          }),
      ])
      ->recordActions([
        EditAction::make()
          ->label('Modifier')
          ->mutateFormDataUsing(function (array $data): array {
            unset($data['video_upload']);

            return $data;
          })
          ->after(function ($record, array $data): void {
            $upload = $data['video_upload'] ?? null;
            $path = is_array($upload) ? ($upload[0] ?? null) : $upload;

            if (is_string($path) && $path !== '' && $record->type === 'video') {
              app(ContentBlockMediaService::class)->attachStoredVideo($record, $path);
            }
          }),
        DeleteAction::make()->label('Supprimer'),
      ])
      ->toolbarActions([
        BulkActionGroup::make([
          DeleteBulkAction::make()->label('Supprimer la sélection'),
        ]),
      ]);
  }
}
