<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PrayerSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrayerSessionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PrayerSession');
    }

    public function view(AuthUser $authUser, PrayerSession $prayerSession): bool
    {
        return $authUser->can('View:PrayerSession');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PrayerSession');
    }

    public function update(AuthUser $authUser, PrayerSession $prayerSession): bool
    {
        return $authUser->can('Update:PrayerSession');
    }

    public function delete(AuthUser $authUser, PrayerSession $prayerSession): bool
    {
        return $authUser->can('Delete:PrayerSession');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PrayerSession');
    }

    public function restore(AuthUser $authUser, PrayerSession $prayerSession): bool
    {
        return $authUser->can('Restore:PrayerSession');
    }

    public function forceDelete(AuthUser $authUser, PrayerSession $prayerSession): bool
    {
        return $authUser->can('ForceDelete:PrayerSession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PrayerSession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PrayerSession');
    }

    public function replicate(AuthUser $authUser, PrayerSession $prayerSession): bool
    {
        return $authUser->can('Replicate:PrayerSession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PrayerSession');
    }

}