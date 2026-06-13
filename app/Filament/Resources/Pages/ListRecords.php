<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasAdminPageHelp;
use Boquizo\FilamentScrollToTop\Traits\ScrollToTop;
use Filament\Resources\Pages\ListRecords as BaseListRecords;

/**
 * Page liste Filament avec description contextuelle.
 */
abstract class ListRecords extends BaseListRecords
{
  use HasAdminPageHelp;
  use ScrollToTop;
}
