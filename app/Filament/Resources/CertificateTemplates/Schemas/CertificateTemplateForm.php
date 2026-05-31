<?php

namespace App\Filament\Resources\CertificateTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CertificateTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('program_id')
                    ->relationship('program', 'name'),
                TextInput::make('name')
                    ->required(),
                Textarea::make('template_body')
                    ->columnSpanFull(),
                Toggle::make('is_default')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

