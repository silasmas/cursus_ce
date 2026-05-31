<?php

namespace App\Filament\Resources\MentorProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MentorProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('max_mentees')
                    ->numeric(),
                Toggle::make('is_accepting_assignments')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('bio')
                    ->label('Biographie (portail fidèle)')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel()
                    ->label('Téléphone'),
                TextInput::make('whatsapp')
                    ->label('WhatsApp (numéro international sans +)'),
                TextInput::make('avatar_path')
                    ->label('Chemin avatar (storage/public)'),
                    ])
                    ->columns(2),
            ]);
    }
}

