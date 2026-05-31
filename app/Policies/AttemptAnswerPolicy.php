<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AttemptAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttemptAnswerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AttemptAnswer');
    }

    public function view(AuthUser $authUser, AttemptAnswer $attemptAnswer): bool
    {
        return $authUser->can('View:AttemptAnswer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AttemptAnswer');
    }

    public function update(AuthUser $authUser, AttemptAnswer $attemptAnswer): bool
    {
        return $authUser->can('Update:AttemptAnswer');
    }

    public function delete(AuthUser $authUser, AttemptAnswer $attemptAnswer): bool
    {
        return $authUser->can('Delete:AttemptAnswer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AttemptAnswer');
    }

    public function restore(AuthUser $authUser, AttemptAnswer $attemptAnswer): bool
    {
        return $authUser->can('Restore:AttemptAnswer');
    }

    public function forceDelete(AuthUser $authUser, AttemptAnswer $attemptAnswer): bool
    {
        return $authUser->can('ForceDelete:AttemptAnswer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AttemptAnswer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AttemptAnswer');
    }

    public function replicate(AuthUser $authUser, AttemptAnswer $attemptAnswer): bool
    {
        return $authUser->can('Replicate:AttemptAnswer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AttemptAnswer');
    }

}