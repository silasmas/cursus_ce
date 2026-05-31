<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CourseModule;
use Illuminate\Auth\Access\HandlesAuthorization;

class CourseModulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CourseModule');
    }

    public function view(AuthUser $authUser, CourseModule $courseModule): bool
    {
        return $authUser->can('View:CourseModule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CourseModule');
    }

    public function update(AuthUser $authUser, CourseModule $courseModule): bool
    {
        return $authUser->can('Update:CourseModule');
    }

    public function delete(AuthUser $authUser, CourseModule $courseModule): bool
    {
        return $authUser->can('Delete:CourseModule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CourseModule');
    }

    public function restore(AuthUser $authUser, CourseModule $courseModule): bool
    {
        return $authUser->can('Restore:CourseModule');
    }

    public function forceDelete(AuthUser $authUser, CourseModule $courseModule): bool
    {
        return $authUser->can('ForceDelete:CourseModule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CourseModule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CourseModule');
    }

    public function replicate(AuthUser $authUser, CourseModule $courseModule): bool
    {
        return $authUser->can('Replicate:CourseModule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CourseModule');
    }

}