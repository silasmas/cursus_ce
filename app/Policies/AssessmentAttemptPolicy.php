<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AssessmentAttempt;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentAttemptPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssessmentAttempt');
    }

    public function view(AuthUser $authUser, AssessmentAttempt $assessmentAttempt): bool
    {
        return $authUser->can('View:AssessmentAttempt');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssessmentAttempt');
    }

    public function update(AuthUser $authUser, AssessmentAttempt $assessmentAttempt): bool
    {
        return $authUser->can('Update:AssessmentAttempt');
    }

    public function delete(AuthUser $authUser, AssessmentAttempt $assessmentAttempt): bool
    {
        return $authUser->can('Delete:AssessmentAttempt');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AssessmentAttempt');
    }

    public function restore(AuthUser $authUser, AssessmentAttempt $assessmentAttempt): bool
    {
        return $authUser->can('Restore:AssessmentAttempt');
    }

    public function forceDelete(AuthUser $authUser, AssessmentAttempt $assessmentAttempt): bool
    {
        return $authUser->can('ForceDelete:AssessmentAttempt');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssessmentAttempt');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssessmentAttempt');
    }

    public function replicate(AuthUser $authUser, AssessmentAttempt $assessmentAttempt): bool
    {
        return $authUser->can('Replicate:AssessmentAttempt');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssessmentAttempt');
    }

}