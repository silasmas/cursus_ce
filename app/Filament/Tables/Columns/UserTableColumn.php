<?php

namespace App\Filament\Tables\Columns;

use App\Models\User;
use App\Support\UserPresentation;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Colonne Filament : avatar (ou initiales) + nom utilisateur.
 */
class UserTableColumn
{
  /**
   * Colonne pour une relation Eloquent (ex. user, askedBy, mentor).
   *
   * @param  string  $relationPath  Chemin relation (user, askedBy, mentor…)
   * @param  string|null  $label  Libellé colonne
   */
  public static function make(string $relationPath, ?string $label = 'Utilisateur'): ViewColumn
  {
    $columnKey = str_replace('.', '_', $relationPath).'_presentation';

    return ViewColumn::make($columnKey)
      ->label($label)
      ->view('filament.tables.columns.user-presentation')
      ->searchable(query: function (Builder $query, string $search) use ($relationPath): Builder {
        return $query->whereHas($relationPath, function (Builder $inner) use ($search): void {
          $inner->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
        });
      })
      ->state(function (Model $record) use ($relationPath): array {
        $user = data_get($record, $relationPath);

        return $user instanceof User
          ? UserPresentation::for($user)
          : UserPresentation::for(null);
      });
  }
}
