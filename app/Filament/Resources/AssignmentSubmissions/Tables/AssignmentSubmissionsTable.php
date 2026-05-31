<?php

namespace App\Filament\Resources\AssignmentSubmissions\Tables;

use App\Enums\SubmissionStatus;
use App\Services\Mentor\MentorTpSubmissionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AssignmentSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assessment.title')
                    ->label('TP')
                    ->searchable(),
                UserTableColumn::make('user', 'Mentoré'),
                UserTableColumn::make('submittedBy', 'Remis par')
                    ->toggleable(),
                TextColumn::make('admin_publication_status')
                    ->label('Publication')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_review' => 'En attente admin',
                        'published' => 'Publié',
                        'rejected' => 'Refusé',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending_review' => 'warning',
                        'published' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('visible_to_mentee')
                    ->label('Visible mentoré')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        SubmissionStatus::Pending->value => 'warning',
                        SubmissionStatus::Approved->value => 'success',
                        SubmissionStatus::Rejected->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('submitted_at')
                    ->label('Remis le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('grade')
                    ->label('Note')
                    ->numeric()
                    ->sortable(),
                UserTableColumn::make('grader', 'Corrigé par')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('publishForMentee')
                    ->label('Publier pour le mentoré')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => $record->admin_publication_status === 'pending_review')
                    ->requiresConfirmation()
                    ->modalHeading('Publier ce TP pour le mentoré ?')
                    ->modalDescription('Le mentoré pourra voir cette remise dans son espace.')
                    ->action(function ($record): void {
                        app(MentorTpSubmissionService::class)->publishForMentee(
                            $record,
                            Auth::user(),
                        );
                    }),
                Action::make('approve')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === SubmissionStatus::Pending->value)
                    ->form([
                        TextInput::make('grade')
                            ->label('Note')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(100),
                        Textarea::make('grader_notes')
                            ->label('Commentaire au fidèle'),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => SubmissionStatus::Approved->value,
                            'grade' => $data['grade'] ?? 100,
                            'grader_notes' => $data['grader_notes'] ?? null,
                            'grader_id' => Auth::id(),
                            'graded_at' => now(),
                        ]);
                    }),
                Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === SubmissionStatus::Pending->value)
                    ->form([
                        Textarea::make('grader_notes')
                            ->label('Motif du refus')
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status' => SubmissionStatus::Rejected->value,
                            'grader_notes' => $data['grader_notes'],
                            'grader_id' => Auth::id(),
                            'graded_at' => now(),
                        ]);
                    }),
                EditAction::make()->label('Modifier'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ]);
    }
}
