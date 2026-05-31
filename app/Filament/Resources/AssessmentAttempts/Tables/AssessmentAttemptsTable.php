<?php

namespace App\Filament\Resources\AssessmentAttempts\Tables;

use App\Enums\AttemptStatus;
use App\Filament\Resources\AssessmentAttempts\AssessmentAttemptResource;
use App\Services\Student\AssessmentAttemptGradingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssessmentAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assessment.title')
                    ->searchable(),
                UserTableColumn::make('user'),
                TextColumn::make('enrollment.id')
                    ->searchable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('passed')
                    ->boolean(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('gradingLockedBy.name')
                    ->label('En correction par')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('grade')
                    ->label('Corriger')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->url(fn ($record) => AssessmentAttemptResource::getUrl('grade', ['record' => $record]))
                    ->visible(fn ($record) => $record->status === AttemptStatus::Submitted->value
                        && app(AssessmentAttemptGradingService::class)->hasUngradedWrittenAnswers($record)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
