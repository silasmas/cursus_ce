<?php

namespace App\Filament\Resources\CourseModules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Un module regroupe plusieurs chapitres. Pour ECAP, configurez le quiz de fin de module dans l\'onglet dédié.')
                    ->schema([
                Select::make('course_id')
                    ->label('Cours')
                    ->relationship('course', 'name')
                    ->required()
                    ->helperText('Cursus auquel appartient ce module (ex. Fondamentaux Apollos pour ECAP).'),
                TextInput::make('name')
                    ->label('Nom du module')
                    ->required()
                    ->helperText('Intitulé du module (ex. « Module 1 — Les bases »).'),
                TextInput::make('sort_order')
                    ->label('Ordre')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText(config('filament_field_help.sort_order')),
                    ])
                    ->columns(2),
            ]);
    }
}

