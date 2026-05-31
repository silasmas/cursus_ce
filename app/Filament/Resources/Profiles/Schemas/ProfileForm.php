<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->schema([
                        Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('prenom')->label('Prénom'),
                        TextInput::make('nom')->label('Nom'),
                        TextInput::make('post_nom')->label('Post-nom'),
                        Select::make('genre')
                            ->label('Genre')
                            ->options([
                                'homme' => 'Homme',
                                'femme' => 'Femme',
                            ]),
                        Select::make('etat_civil')
                            ->label('État civil')
                            ->options([
                                'celibataire' => 'Célibataire',
                                'marie' => 'Marié(e)',
                                'veuf' => 'Veuf / veuve',
                                'divorce' => 'Divorcé(e)',
                                'union_libre' => 'Union libre',
                            ]),
                        TextInput::make('nationalite')->label('Nationalité'),
                        TextInput::make('nationalite_autre')->label('Nationalité (précision)'),
                        TextInput::make('lieu_naissance')->label('Lieu de naissance'),
                        DatePicker::make('date_naissance')->label('Date de naissance')->native(false),
                    ])
                    ->columns(2),
                Section::make('Coordonnées & résidence')
                    ->schema([
                        TextInput::make('phone')->label('Téléphone')->tel(),
                        TextInput::make('contact_email')->label('E-mail de contact')->email(),
                        TextInput::make('profession')->label('Profession'),
                        TextInput::make('commune_habitation')->label('Commune'),
                        TextInput::make('quartier_habitation')->label('Quartier'),
                        TextInput::make('adresse_numero_avenue')->label('Adresse')->columnSpanFull(),
                        Select::make('academic_session_id')
                            ->label('Session académique')
                            ->relationship('academicSession', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                Section::make('Parcours spirituel')
                    ->schema([
                        Toggle::make('est_ne_de_nouveau')->label('Né de nouveau'),
                        TextInput::make('annee_nouvelle_naissance')->label('Année nouvelle naissance'),
                        Textarea::make('eglise_acceptation_jesus')
                            ->label('Église à l\'acceptation de Jésus')
                            ->columnSpanFull(),
                        Toggle::make('est_baptise_eau')->label('Baptisé(e) dans l\'eau'),
                        TextInput::make('eglise_bapteme')->label('Église du baptême'),
                        Toggle::make('est_passe_metamorphoo')->label('Passé par Metamorphoo'),
                        TextInput::make('mentor_metamorphoo_nom')->label('Mentor Metamorphoo'),
                        Toggle::make('souhaite_faire_metamorphoo')->label('Souhaite faire Metamorphoo'),
                        TextInput::make('eglise_attache')->label('Église d\'attache'),
                        TextInput::make('eglise_attache_autre')->label('Église d\'attache (autre)'),
                        Toggle::make('souhaite_oeuvrer_phila_apres_apollos')
                            ->label('Souhaite oeuvrer Phila après Apollos'),
                    ])
                    ->columns(2),
                Section::make('Inscription & métadonnées')
                    ->schema([
                        TextInput::make('vacation_choice')->label('Choix de vacation'),
                        TextInput::make('vacation_autre')->label('Vacation (autre)'),
                        TextInput::make('google_form_response_id')->label('ID réponse Google Form')->columnSpanFull(),
                        DateTimePicker::make('inscription_submitted_at')->label('Inscription soumise le')->columnSpanFull(),
                        Textarea::make('inscription_source_payload')
                            ->label('Payload source (JSON)')
                            ->rows(8)
                            ->columnSpanFull()
                            ->formatStateUsing(fn (?array $state): string => $state !== null && $state !== []
                                ? (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : '')
                            ->dehydrateStateUsing(function (?string $state): ?array {
                                if ($state === null || trim($state) === '') {
                                    return null;
                                }

                                $decoded = json_decode($state, true);

                                return is_array($decoded) ? $decoded : null;
                            }),
                        TextInput::make('avatar_path')->label('Avatar')->columnSpanFull(),
                        Textarea::make('bio')->label('Biographie')->columnSpanFull(),
                        TextInput::make('locale')->label('Langue'),
                        Textarea::make('metadata')->label('Métadonnées')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
