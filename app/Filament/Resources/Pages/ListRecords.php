<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasAdminPageHelp;
use Filament\Resources\Pages\ListRecords as BaseListRecords;

/**
 * Page liste Filament avec description contextuelle.
 */
abstract class ListRecords extends BaseListRecords
{
  use HasAdminPageHelp;
}
