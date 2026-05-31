<?php

namespace App\Filament\Resources\AssignmentSubmissions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssignmentSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Remise')
                    ->description('Travail pratique remis par le fidèle ou son mentor.')
                    ->schema([
                        Select::make('assessment_id')
                            ->label('Évaluation / TP')
                            ->relationship('assessment', 'title')
                            ->required(),
                        Select::make('user_id')
                            ->label('Mentoré (fidèle)')
                            ->relationship('user', 'name')
                            ->required(),
                        Select::make('submitted_by_user_id')
                            ->label('Remis par')
                            ->relationship('submittedBy', 'name'),
                        Select::make('enrollment_id')
                            ->label('Inscription')
                            ->relationship('enrollment', 'id'),
                        TextInput::make('version')
                            ->label('Version')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('file_path')
                            ->label('Fichier (chemin storage)'),
                        DateTimePicker::make('submitted_at')
                            ->label('Date de remise'),
                    ])
                    ->columns(2),
                Section::make('Publication & correction')
                    ->schema([
                        Select::make('admin_publication_status')
                            ->label('Publication mentoré')
                            ->options([
                                'published' => 'Publié — visible par le mentoré',
                                'pending_review' => 'En attente — remise par le mentor',
                                'rejected' => 'Refusé par l\'administration',
                            ])
                            ->default('published')
                            ->required(),
                        Toggle::make('visible_to_mentee')
                            ->label('Visible par le mentoré')
                            ->default(true),
                        Select::make('status')
                            ->label('Statut pédagogique')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => 'Validé',
                                'rejected' => 'Refusé',
                            ]),
                        TextInput::make('grade')
                            ->label('Note')
                            ->numeric(),
                        Textarea::make('grader_notes')
                            ->label('Commentaire formateur')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
