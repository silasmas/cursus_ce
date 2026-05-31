<?php

namespace App\Filament\Resources\MentorAssignments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MentorAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                UserTableColumn::make('mentor', 'Mentor'),
                UserTableColumn::make('mentee', 'Mentoré'),
                TextColumn::make('program.name')
                    ->searchable(),
                TextColumn::make('enrollment.id')
                    ->searchable(),
                TextColumn::make('assigned_by_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('assignment_mode')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ended_at')
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
