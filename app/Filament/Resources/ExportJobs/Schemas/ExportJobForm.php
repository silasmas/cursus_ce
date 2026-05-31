<?php

namespace App\Filament\Resources\ExportJobs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExportJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('user_id')
                    ->relationship('user', 'name'),
                TextInput::make('type')
                    ->required(),
                TextInput::make('file_path'),
                TextInput::make('status')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

