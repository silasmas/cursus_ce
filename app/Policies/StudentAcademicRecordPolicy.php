<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StudentAcademicRecord;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentAcademicRecordPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StudentAcademicRecord');
    }

    public function view(AuthUser $authUser, StudentAcademicRecord $studentAcademicRecord): bool
    {
        return $authUser->can('View:StudentAcademicRecord');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StudentAcademicRecord');
    }

    public function update(AuthUser $authUser, StudentAcademicRecord $studentAcademicRecord): bool
    {
        return $authUser->can('Update:StudentAcademicRecord');
    }

    public function delete(AuthUser $authUser, StudentAcademicRecord $studentAcademicRecord): bool
    {
        return $authUser->can('Delete:StudentAcademicRecord');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StudentAcademicRecord');
    }

    public function restore(AuthUser $authUser, StudentAcademicRecord $studentAcademicRecord): bool
    {
        return $authUser->can('Restore:StudentAcademicRecord');
    }

    public function forceDelete(AuthUser $authUser, StudentAcademicRecord $studentAcademicRecord): bool
    {
        return $authUser->can('ForceDelete:StudentAcademicRecord');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StudentAcademicRecord');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StudentAcademicRecord');
    }

    public function replicate(AuthUser $authUser, StudentAcademicRecord $studentAcademicRecord): bool
    {
        return $authUser->can('Replicate:StudentAcademicRecord');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StudentAcademicRecord');
    }

}