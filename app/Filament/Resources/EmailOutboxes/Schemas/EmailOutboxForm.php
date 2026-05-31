<?php

namespace App\Filament\Resources\EmailOutboxes\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailOutboxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                TextInput::make('to_email')
                    ->email()
                    ->required(),
                TextInput::make('subject'),
                Textarea::make('body')
                    ->columnSpanFull(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required(),
                DateTimePicker::make('sent_at'),
                TextInput::make('attempts')
                    ->required()
                    ->numeric()
                    ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}

