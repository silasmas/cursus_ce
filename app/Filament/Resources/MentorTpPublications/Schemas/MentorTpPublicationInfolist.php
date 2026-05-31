<?php

namespace App\Filament\Resources\MentorTpPublications\Schemas;

use App\Filament\Infolists\Components\UserInfolistEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Affichage en lecture seule d'une remise de TP mentor.
 */
class MentorTpPublicationInfolist
{
  /**
   * Configure l'infolist Filament pour la page de détail.
   */
  public static function configure(Schema $schema): Schema
  {
    return $schema
      ->components([
        Section::make('Informations générales')
          ->schema([
            TextEntry::make('assessment.title')
              ->label('Travail pratique'),
            UserInfolistEntry::make('user', 'Mentoré'),
            UserInfolistEntry::make('submittedBy', 'Mentor'),
            TextEntry::make('admin_publication_status')
              ->label('Statut publication')
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
            TextEntry::make('submitted_at')
              ->label('Remis le')
              ->dateTime('d/m/Y H:i'),
            TextEntry::make('version')
              ->label('Version'),
          ])
          ->columns(2),
        Section::make('Contenu de la remise')
          ->schema([
            TextEntry::make('answer_text')
              ->label('Réponse / commentaire')
              ->placeholder('Aucun texte saisi')
              ->columnSpanFull()
              ->prose(),
            IconEntry::make('has_attached_file')
              ->label('Fichier joint')
              ->boolean()
              ->getStateUsing(fn ($record): bool => $record->hasAttachedFile())
              ->trueIcon('heroicon-o-paper-clip')
              ->falseIcon('heroicon-o-x-mark')
              ->trueColor('success')
              ->falseColor('gray'),
            TextEntry::make('file_path')
              ->label('Nom du fichier')
              ->placeholder('Aucun fichier')
              ->visible(fn ($record): bool => $record->hasAttachedFile()),
            TextEntry::make('file_url')
              ->label('Fichier')
              ->formatStateUsing(fn (): string => 'Ouvrir le fichier joint')
              ->url(fn ($record): ?string => $record->file_url)
              ->openUrlInNewTab()
              ->icon('heroicon-o-arrow-top-right-on-square')
              ->color('primary')
              ->visible(fn ($record): bool => $record->hasAttachedFile()),
          ])
          ->columns(2),
      ]);
  }
}
