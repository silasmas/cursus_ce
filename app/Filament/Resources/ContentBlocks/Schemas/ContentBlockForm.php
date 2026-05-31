<?php

namespace App\Filament\Resources\ContentBlocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'un bloc de contenu pédagogique.
 */
class ContentBlockForm
{
  /**
   * Types de blocs disponibles (libellés français).
   *
   * @return array<string, string>
   */
  private static function typeOptions(): array
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
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.content_block');

    return $schema
      ->components([
        Section::make('Contenu du chapitre')
          ->description('Élément affiché dans un chapitre : texte, média ou lien.')
          ->schema([
            Select::make('chapter_id')
              ->label('Chapitre')
              ->relationship('chapter', 'title')
              ->required()
              ->searchable()
              ->preload()
              ->helperText($help['chapter_id']),
            Select::make('type')
              ->label('Type')
              ->options(self::typeOptions())
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
              ->label('URL externe')
              ->url()
              ->helperText($help['url']),
            Textarea::make('metadata')
              ->label('Métadonnées (JSON)')
              ->columnSpanFull()
              ->helperText($help['metadata']),
          ])
          ->columns(2),
      ]);
  }
}
