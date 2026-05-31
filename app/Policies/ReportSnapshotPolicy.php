<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ReportSnapshot;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportSnapshotPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ReportSnapshot');
    }

    public function view(AuthUser $authUser, ReportSnapshot $reportSnapshot): bool
    {
        return $authUser->can('View:ReportSnapshot');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ReportSnapshot');
    }

    public function update(AuthUser $authUser, ReportSnapshot $reportSnapshot): bool
    {
        return $authUser->can('Update:ReportSnapshot');
    }

    public function delete(AuthUser $authUser, ReportSnapshot $reportSnapshot): bool
    {
        return $authUser->can('Delete:ReportSnapshot');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ReportSnapshot');
    }

    public function restore(AuthUser $authUser, ReportSnapshot $reportSnapshot): bool
    {
        return $authUser->can('Restore:ReportSnapshot');
    }

    public function forceDelete(AuthUser $authUser, ReportSnapshot $reportSnapshot): bool
    {
        return $authUser->can('ForceDelete:ReportSnapshot');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ReportSnapshot');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ReportSnapshot');
    }

    public function replicate(AuthUser $authUser, ReportSnapshot $reportSnapshot): bool
    {
        return $authUser->can('Replicate:ReportSnapshot');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ReportSnapshot');
    }

}