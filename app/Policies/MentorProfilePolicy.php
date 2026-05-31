<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MentorProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentorProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MentorProfile');
    }

    public function view(AuthUser $authUser, MentorProfile $mentorProfile): bool
    {
        return $authUser->can('View:MentorProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MentorProfile');
    }

    public function update(AuthUser $authUser, MentorProfile $mentorProfile): bool
    {
        return $authUser->can('Update:MentorProfile');
    }

    public function delete(AuthUser $authUser, MentorProfile $mentorProfile): bool
    {
        return $authUser->can('Delete:MentorProfile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MentorProfile');
    }

    public function restore(AuthUser $authUser, MentorProfile $mentorProfile): bool
    {
        return $authUser->can('Restore:MentorProfile');
    }

    public function forceDelete(AuthUser $authUser, MentorProfile $mentorProfile): bool
    {
        return $authUser->can('ForceDelete:MentorProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MentorProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MentorProfile');
    }

    public function replicate(AuthUser $authUser, MentorProfile $mentorProfile): bool
    {
        return $authUser->can('Replicate:MentorProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MentorProfile');
    }

}