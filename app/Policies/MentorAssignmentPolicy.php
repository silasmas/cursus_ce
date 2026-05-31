<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MentorAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentorAssignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MentorAssignment');
    }

    public function view(AuthUser $authUser, MentorAssignment $mentorAssignment): bool
    {
        return $authUser->can('View:MentorAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MentorAssignment');
    }

    public function update(AuthUser $authUser, MentorAssignment $mentorAssignment): bool
    {
        return $authUser->can('Update:MentorAssignment');
    }

    public function delete(AuthUser $authUser, MentorAssignment $mentorAssignment): bool
    {
        return $authUser->can('Delete:MentorAssignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MentorAssignment');
    }

    public function restore(AuthUser $authUser, MentorAssignment $mentorAssignment): bool
    {
        return $authUser->can('Restore:MentorAssignment');
    }

    public function forceDelete(AuthUser $authUser, MentorAssignment $mentorAssignment): bool
    {
        return $authUser->can('ForceDelete:MentorAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MentorAssignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MentorAssignment');
    }

    public function replicate(AuthUser $authUser, MentorAssignment $mentorAssignment): bool
    {
        return $authUser->can('Replicate:MentorAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MentorAssignment');
    }

}