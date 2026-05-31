<?php

namespace App\Filament\Resources\PrayerSessionAttendees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrayerSessionAttendeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('prayer_session_id')
                    ->relationship('prayerSession', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Toggle::make('attended')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

