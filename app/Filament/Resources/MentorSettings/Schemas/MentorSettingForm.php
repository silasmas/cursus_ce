<?php

namespace App\Filament\Resources\MentorSettings\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire des paramètres mentorat.
 */
class MentorSettingForm
{
  /**
   * Configure le formulaire.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Canaux de rendez-vous')
          ->description('Choisissez les moyens de réunion visibles côté mentor.')
          ->schema([
            CheckboxList::make('visible_channels')
              ->label('Canaux visibles')
              ->options([
                'whatsapp' => 'WhatsApp',
                'zoom' => 'Zoom',
                'google_meet' => 'Google Meet',
              ])
              ->required()
              ->columns(3),
            Toggle::make('zoom_auto_create_link')
              ->label('Créer automatiquement un lien Zoom')
              ->inline(false)
              ->helperText('Nécessite ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID et ZOOM_CLIENT_SECRET dans le .env.'),
            Toggle::make('google_meet_auto_create_link')
              ->label('Créer automatiquement un lien Google Meet')
              ->inline(false)
              ->helperText('Nécessite GOOGLE_CALENDAR_CLIENT_ID, GOOGLE_CALENDAR_CLIENT_SECRET et GOOGLE_CALENDAR_REFRESH_TOKEN dans le .env.'),
            TextInput::make('google_meet_help')
              ->label('Aide Google Meet (affichée au mentor)')
              ->maxLength(255)
              ->placeholder('Ex: Le lien Meet sera généré automatiquement si activé ci-dessus.'),
            TextInput::make('whatsapp_help')
              ->label('Aide WhatsApp')
              ->maxLength(255)
              ->placeholder('Ex: Collez le lien WhatsApp manuellement.'),
          ]),
        Section::make('Notifications rendez-vous')
          ->description('Comportement chez le destinataire quand un RDV est créé ou qu\'une réponse est envoyée.')
          ->schema([
            Toggle::make('notify_with_email')
              ->label('Envoyer aussi un email')
              ->inline(false),
            Toggle::make('notify_with_sound')
              ->label('Jouer un son dans la cloche')
              ->inline(false),
            Toggle::make('notify_with_blink')
              ->label('Faire clignoter le titre et la cloche')
              ->inline(false),
          ])
          ->columns(3),
      ]);
  }
}

