<?php

namespace App\Filament\Infolists\Components;

use App\Models\User;
use App\Support\UserPresentation;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\Model;

/**
 * Entrée Filament : avatar (ou initiales) + nom utilisateur.
 */
class UserInfolistEntry
{
  /**
   * Entrée pour une relation Eloquent (ex. user, submittedBy).
   *
   * @param  string  $relationPath  Chemin relation
   * @param  string|null  $label  Libellé
   */
  public static function make(string $relationPath, ?string $label = 'Utilisateur'): ViewEntry
  {
    $entryKey = str_replace('.', '_', $relationPath).'_presentation';

    return ViewEntry::make($entryKey)
      ->label($label)
      ->view('filament.tables.columns.user-presentation')
      ->state(function (Model $record) use ($relationPath): array {
        $user = data_get($record, $relationPath);

        return $user instanceof User
          ? UserPresentation::for($user)
          : UserPresentation::for(null);
      });
  }
}
