<?php

namespace App\Filament\Resources\Profiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Tables\Columns\UserTableColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_label')
                    ->label('Nom')
                    ->searchable(query: function ($query, string $search): void {
                        $query->where(function ($q) use ($search): void {
                            $q->where('prenom', 'like', "%{$search}%")
                                ->orWhere('nom', 'like', "%{$search}%")
                                ->orWhere('post_nom', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                        });
                    }),
                UserTableColumn::make('user'),
                TextColumn::make('academicSession.name')
                    ->label('Session')
                    ->sortable(),
                TextColumn::make('vacation_choice')
                    ->label('Vacation')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('locale')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
