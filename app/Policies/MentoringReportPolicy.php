<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MentoringReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class MentoringReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MentoringReport');
    }

    public function view(AuthUser $authUser, MentoringReport $mentoringReport): bool
    {
        return $authUser->can('View:MentoringReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MentoringReport');
    }

    public function update(AuthUser $authUser, MentoringReport $mentoringReport): bool
    {
        return $authUser->can('Update:MentoringReport');
    }

    public function delete(AuthUser $authUser, MentoringReport $mentoringReport): bool
    {
        return $authUser->can('Delete:MentoringReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MentoringReport');
    }

    public function restore(AuthUser $authUser, MentoringReport $mentoringReport): bool
    {
        return $authUser->can('Restore:MentoringReport');
    }

    public function forceDelete(AuthUser $authUser, MentoringReport $mentoringReport): bool
    {
        return $authUser->can('ForceDelete:MentoringReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MentoringReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MentoringReport');
    }

    public function replicate(AuthUser $authUser, MentoringReport $mentoringReport): bool
    {
        return $authUser->can('Replicate:MentoringReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MentoringReport');
    }

}