<?php

namespace App\Filament\Resources\StudentAcademicRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentAcademicRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academicSession.name')
                    ->searchable(),
                UserTableColumn::make('user'),
                TextColumn::make('final_average')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('validated_at')
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
