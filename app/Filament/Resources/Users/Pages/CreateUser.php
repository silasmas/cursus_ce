<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            throw ValidationException::withMessages([
                'password' => __('validation.required', ['attribute' => 'password']),
            ]);
        }

        return $data;
    }
}
