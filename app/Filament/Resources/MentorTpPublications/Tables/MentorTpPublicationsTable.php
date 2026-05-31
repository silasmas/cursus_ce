<?php

namespace App\Filament\Resources\MentorTpPublications\Tables;

use App\Filament\Resources\MentorTpPublications\MentorTpPublicationResource;
use App\Services\Mentor\MentorTpSubmissionService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

/**
 * Tableau des TP remis par les mentors en attente de publication.
 */
class MentorTpPublicationsTable
{
  /**
   * Configure la table Filament.
   */
  public static function configure(Table $table): Table
  {
    return $table
      ->defaultSort('submitted_at', 'desc')
      ->columns([
        TextColumn::make('assessment.title')
          ->label('Travail pratique')
          ->searchable(),
        UserTableColumn::make('user', 'Mentoré'),
        UserTableColumn::make('submittedBy', 'Mentor'),
        TextColumn::make('admin_publication_status')
          ->label('Statut')
          ->badge()
          ->formatStateUsing(fn (string $state): string => match ($state) {
            'pending_review' => 'En attente',
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
        TextColumn::make('submitted_at')
          ->label('Remis le')
          ->dateTime('d/m/Y H:i')
          ->sortable(),
        IconColumn::make('has_attached_file')
          ->label('Fichier joint')
          ->boolean()
          ->getStateUsing(fn ($record): bool => $record->hasAttachedFile())
          ->trueIcon('heroicon-o-paper-clip')
          ->falseIcon('heroicon-o-minus')
          ->trueColor('success')
          ->falseColor('gray')
          ->tooltip(fn ($record): string => $record->hasAttachedFile()
            ? 'Fichier joint — cliquez sur « Ouvrir » ou « Voir le détail »'
            : 'Aucun fichier joint'),
        TextColumn::make('answer_text')
          ->label('Contenu')
          ->limit(40)
          ->toggleable(),
      ])
      ->filters([
        SelectFilter::make('admin_publication_status')
          ->label('Statut')
          ->options([
            'pending_review' => 'En attente',
            'published' => 'Publié',
            'rejected' => 'Refusé',
          ])
          ->default('pending_review'),
      ])
      ->recordActions([
        Action::make('viewDetail')
          ->label('Voir le détail')
          ->icon('heroicon-o-eye')
          ->color('gray')
          ->url(fn ($record): string => MentorTpPublicationResource::getUrl('view', ['record' => $record])),
        Action::make('openFile')
          ->label('Ouvrir le fichier')
          ->icon('heroicon-o-document-arrow-down')
          ->color('info')
          ->url(fn ($record): ?string => $record->file_url)
          ->openUrlInNewTab()
          ->visible(fn ($record): bool => $record->hasAttachedFile()),
        Action::make('publishForMentee')
          ->label('Publier pour le mentoré')
          ->icon('heroicon-o-check-badge')
          ->color('success')
          ->visible(fn ($record) => $record->admin_publication_status === 'pending_review')
          ->requiresConfirmation()
          ->modalHeading('Valider et publier ce TP ?')
          ->modalDescription('Le mentoré pourra consulter cette remise dans son espace.')
          ->action(function ($record): void {
            app(MentorTpSubmissionService::class)->publishForMentee($record, Auth::user());
          }),
        Action::make('reject')
          ->label('Refuser')
          ->icon('heroicon-o-x-circle')
          ->color('danger')
          ->visible(fn ($record) => $record->admin_publication_status === 'pending_review')
          ->requiresConfirmation()
          ->action(function ($record): void {
            $record->update(['admin_publication_status' => 'rejected']);
          }),
      ])
      ->toolbarActions([
        BulkAction::make('publishSelected')
          ->label('Publier la sélection')
          ->icon('heroicon-o-check-badge')
          ->color('success')
          ->requiresConfirmation()
          ->modalHeading('Publier les TP sélectionnés ?')
          ->modalDescription('Chaque mentoré et son mentor seront notifiés par l\'application et par e-mail.')
          ->action(function (\Illuminate\Support\Collection $records): void {
            $pending = $records->filter(
              fn ($record) => $record->admin_publication_status === 'pending_review',
            );

            if ($pending->isEmpty()) {
              return;
            }

            app(MentorTpSubmissionService::class)->publishForMentee($pending, Auth::user());
          }),
      ]);
  }
}
