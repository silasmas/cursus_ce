<?php

namespace App\Filament\Resources\ReportSnapshots\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportSnapshotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                TextInput::make('scope')
                    ->required(),
                TextInput::make('scope_id')
                    ->numeric(),
                TextInput::make('period'),
                Textarea::make('metrics')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('generated_at')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

