<?php

namespace App\Filament\Resources\EmailOutboxes\Pages;

use App\Filament\Resources\EmailOutboxes\EmailOutboxResource;
use App\Filament\Resources\Pages\CreateRecord;

class CreateEmailOutbox extends CreateRecord
{
    protected static string $resource = EmailOutboxResource::class;
}
