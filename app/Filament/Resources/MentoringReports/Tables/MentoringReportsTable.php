<?php

namespace App\Filament\Resources\MentoringReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MentoringReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mentorAssignment.id')
                    ->searchable(),
                TextColumn::make('chapter.title')
                    ->searchable(),
                TextColumn::make('report_kind')
                    ->searchable(),
                UserTableColumn::make('author', 'Auteur'),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
