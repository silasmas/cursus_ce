<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CertificateForm
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
                Select::make('program_id')
                    ->relationship('program', 'name')
                    ->required(),
                Select::make('academic_session_id')
                    ->relationship('academicSession', 'name'),
                Select::make('enrollment_id')
                    ->relationship('enrollment', 'id'),
                Select::make('certificate_template_id')
                    ->relationship('certificateTemplate', 'name'),
                TextInput::make('number')
                    ->required(),
                DateTimePicker::make('issued_at')
                    ->required(),
                TextInput::make('pdf_path'),
                    ])
                    ->columns(2),
            ]);
    }
}

