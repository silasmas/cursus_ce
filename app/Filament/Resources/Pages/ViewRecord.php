<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasAdminPageHelp;
use Filament\Resources\Pages\ViewRecord as BaseViewRecord;

/**
 * Page consultation Filament avec description contextuelle.
 */
abstract class ViewRecord extends BaseViewRecord
{
  use HasAdminPageHelp;
}
