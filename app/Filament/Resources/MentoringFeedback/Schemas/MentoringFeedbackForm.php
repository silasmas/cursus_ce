<?php

namespace App\Filament\Resources\MentoringFeedback\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MentoringFeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les champs ci-dessous.')
                    ->schema([
                //
                    ])
                    ->columns(2),
            ]);
    }
}

