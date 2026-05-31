<?php

namespace App\Filament\Resources\LearningGroupMembers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LearningGroupMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                Select::make('learning_group_id')
                    ->relationship('learningGroup', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('group_role')
                    ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}

