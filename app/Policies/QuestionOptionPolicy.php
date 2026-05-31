<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuestionOption;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionOptionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuestionOption');
    }

    public function view(AuthUser $authUser, QuestionOption $questionOption): bool
    {
        return $authUser->can('View:QuestionOption');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuestionOption');
    }

    public function update(AuthUser $authUser, QuestionOption $questionOption): bool
    {
        return $authUser->can('Update:QuestionOption');
    }

    public function delete(AuthUser $authUser, QuestionOption $questionOption): bool
    {
        return $authUser->can('Delete:QuestionOption');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:QuestionOption');
    }

    public function restore(AuthUser $authUser, QuestionOption $questionOption): bool
    {
        return $authUser->can('Restore:QuestionOption');
    }

    public function forceDelete(AuthUser $authUser, QuestionOption $questionOption): bool
    {
        return $authUser->can('ForceDelete:QuestionOption');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuestionOption');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuestionOption');
    }

    public function replicate(AuthUser $authUser, QuestionOption $questionOption): bool
    {
        return $authUser->can('Replicate:QuestionOption');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuestionOption');
    }

}