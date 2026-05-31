<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire d'un fichier média (bibliothèque).
 */
class MediaAssetForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.media_asset');

    return $schema
      ->components([
        Section::make('Fichier média')
          ->description('Bibliothèque de fichiers réutilisables dans les blocs de contenu (PDF, vidéos, images…).')
          ->schema([
            TextInput::make('disk')
              ->label('Disque')
              ->required()
              ->default('public')
              ->helperText($help['disk']),
            TextInput::make('path')
              ->label('Chemin')
              ->required()
              ->helperText($help['path']),
            TextInput::make('mime_type')
              ->label('Type MIME')
              ->helperText($help['mime_type']),
            TextInput::make('size_bytes')
              ->label('Taille (octets)')
              ->numeric()
              ->helperText($help['size_bytes']),
            TextInput::make('duration_seconds')
              ->label('Durée (secondes)')
              ->numeric()
              ->helperText($help['duration_seconds']),
            TextInput::make('transcode_status')
              ->label('État du transcodage')
              ->helperText($help['transcode_status']),
          ])
          ->columns(2),
      ]);
  }
}
