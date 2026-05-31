<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasAdminPageHelp;
use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;

/**
 * Page création Filament avec description contextuelle.
 */
abstract class CreateRecord extends BaseCreateRecord
{
  use HasAdminPageHelp;
}
