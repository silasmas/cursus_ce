<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PrayerSessionAttendee;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrayerSessionAttendeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PrayerSessionAttendee');
    }

    public function view(AuthUser $authUser, PrayerSessionAttendee $prayerSessionAttendee): bool
    {
        return $authUser->can('View:PrayerSessionAttendee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PrayerSessionAttendee');
    }

    public function update(AuthUser $authUser, PrayerSessionAttendee $prayerSessionAttendee): bool
    {
        return $authUser->can('Update:PrayerSessionAttendee');
    }

    public function delete(AuthUser $authUser, PrayerSessionAttendee $prayerSessionAttendee): bool
    {
        return $authUser->can('Delete:PrayerSessionAttendee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PrayerSessionAttendee');
    }

    public function restore(AuthUser $authUser, PrayerSessionAttendee $prayerSessionAttendee): bool
    {
        return $authUser->can('Restore:PrayerSessionAttendee');
    }

    public function forceDelete(AuthUser $authUser, PrayerSessionAttendee $prayerSessionAttendee): bool
    {
        return $authUser->can('ForceDelete:PrayerSessionAttendee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PrayerSessionAttendee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PrayerSessionAttendee');
    }

    public function replicate(AuthUser $authUser, PrayerSessionAttendee $prayerSessionAttendee): bool
    {
        return $authUser->can('Replicate:PrayerSessionAttendee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PrayerSessionAttendee');
    }

}