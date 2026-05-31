<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasAdminPageHelp;
use Filament\Resources\Pages\EditRecord as BaseEditRecord;

/**
 * Page édition Filament avec description contextuelle.
 */
abstract class EditRecord extends BaseEditRecord
{
  use HasAdminPageHelp;
}
