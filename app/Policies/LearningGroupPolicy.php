<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningGroupPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningGroup');
    }

    public function view(AuthUser $authUser, LearningGroup $learningGroup): bool
    {
        return $authUser->can('View:LearningGroup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningGroup');
    }

    public function update(AuthUser $authUser, LearningGroup $learningGroup): bool
    {
        return $authUser->can('Update:LearningGroup');
    }

    public function delete(AuthUser $authUser, LearningGroup $learningGroup): bool
    {
        return $authUser->can('Delete:LearningGroup');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningGroup');
    }

    public function restore(AuthUser $authUser, LearningGroup $learningGroup): bool
    {
        return $authUser->can('Restore:LearningGroup');
    }

    public function forceDelete(AuthUser $authUser, LearningGroup $learningGroup): bool
    {
        return $authUser->can('ForceDelete:LearningGroup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningGroup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningGroup');
    }

    public function replicate(AuthUser $authUser, LearningGroup $learningGroup): bool
    {
        return $authUser->can('Replicate:LearningGroup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningGroup');
    }

}