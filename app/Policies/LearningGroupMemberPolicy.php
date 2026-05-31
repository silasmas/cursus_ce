<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningGroupMember;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningGroupMemberPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningGroupMember');
    }

    public function view(AuthUser $authUser, LearningGroupMember $learningGroupMember): bool
    {
        return $authUser->can('View:LearningGroupMember');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningGroupMember');
    }

    public function update(AuthUser $authUser, LearningGroupMember $learningGroupMember): bool
    {
        return $authUser->can('Update:LearningGroupMember');
    }

    public function delete(AuthUser $authUser, LearningGroupMember $learningGroupMember): bool
    {
        return $authUser->can('Delete:LearningGroupMember');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningGroupMember');
    }

    public function restore(AuthUser $authUser, LearningGroupMember $learningGroupMember): bool
    {
        return $authUser->can('Restore:LearningGroupMember');
    }

    public function forceDelete(AuthUser $authUser, LearningGroupMember $learningGroupMember): bool
    {
        return $authUser->can('ForceDelete:LearningGroupMember');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningGroupMember');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningGroupMember');
    }

    public function replicate(AuthUser $authUser, LearningGroupMember $learningGroupMember): bool
    {
        return $authUser->can('Replicate:LearningGroupMember');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningGroupMember');
    }

}