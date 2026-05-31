<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VacationQuestion;
use Illuminate\Auth\Access\HandlesAuthorization;

class VacationQuestionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VacationQuestion');
    }

    public function view(AuthUser $authUser, VacationQuestion $vacationQuestion): bool
    {
        return $authUser->can('View:VacationQuestion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VacationQuestion');
    }

    public function update(AuthUser $authUser, VacationQuestion $vacationQuestion): bool
    {
        return $authUser->can('Update:VacationQuestion');
    }

    public function delete(AuthUser $authUser, VacationQuestion $vacationQuestion): bool
    {
        return $authUser->can('Delete:VacationQuestion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VacationQuestion');
    }

    public function restore(AuthUser $authUser, VacationQuestion $vacationQuestion): bool
    {
        return $authUser->can('Restore:VacationQuestion');
    }

    public function forceDelete(AuthUser $authUser, VacationQuestion $vacationQuestion): bool
    {
        return $authUser->can('ForceDelete:VacationQuestion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VacationQuestion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VacationQuestion');
    }

    public function replicate(AuthUser $authUser, VacationQuestion $vacationQuestion): bool
    {
        return $authUser->can('Replicate:VacationQuestion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VacationQuestion');
    }

}