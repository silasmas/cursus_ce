<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Formulaire de gestion des comptes utilisateurs.
 */
class UserForm
{
  /**
   * Configure le schéma Filament.
   */
  public static function configure(Schema $schema): Schema
  {
    $help = config('filament_field_help.user');

    return $schema
      ->components([
        Section::make('Informations générales')
          ->description('Compte de connexion au portail fidèle, à l\'espace acteurs ECAP ou au panneau admin.')
          ->schema([
            TextInput::make('name')
              ->label('Nom complet')
              ->required()
              ->helperText($help['name']),
            TextInput::make('email')
              ->label('Adresse e-mail')
              ->email()
              ->required()
              ->helperText($help['email']),
            DateTimePicker::make('email_verified_at')
              ->label('E-mail vérifié le')
              ->helperText($help['email_verified_at']),
            TextInput::make('password')
              ->label('Mot de passe')
              ->password()
              ->minLength(8)
              ->dehydrated(fn (?string $state): bool => filled($state))
              ->helperText($help['password']),
          ])
          ->columns(2),
      ]);
  }
}
