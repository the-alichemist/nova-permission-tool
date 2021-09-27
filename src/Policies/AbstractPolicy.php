<?php

declare(strict_types=1);

namespace DigitalCloud\PermissionTool\Policies;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbstractPolicy
{
    use HandlesAuthorization;
    public $resource;

    public function resourceClass()
    {
        return $this->resource;
    }
    
    public function check($permission, $record = null)
    {
        $resource = $this->resourceClass();
        $permission = sprintf('%s-%s', $permission, $resource);

        if (!$record) {
            return Gate::check($permission);
        }
        
        $userIdCol = config('permission.column_names.user_id');
        $hasUserAttribute = array_key_exists($userIdCol, $record->getAttributes());

        if (Gate::check($permission)) {
            if (!$hashasUserAttributeUser) {
                return true;
            }
            if ($record->$userIdCol == request()->user()->id) {
                return true;
            }
        }

        return false;
    }

    public function viewAny($user): bool
    {
        if ($this->check('view') || $this->check('viewAny')) {
            return true;
        }

        return false;
    }

    public function view($user, $record): bool
    {
        if ($this->check('viewAny') || $this->check('view', $record)) {
            return true;
        }

        return false;
    }

    public function create(): bool
    {
        if ($this->check('create')) {
            return true;
        }

        return false;
    }

    public function update($user, $record): bool
    {
        \Log::info('update called');
        if ($this->check('updateAny') || $this->check('update', $record)) {
            return true;
        }

        return false;
    }

    public function delete($user, $record): bool
    {
        \Log::info('delete called');
        if ($this->check('deleteAny') || $this->check('delete', $record)) {
            return true;
        }

        return true;
    }

    public function restore(): bool
    {
        if ($this->check('restore')) {
            return true;
        }

        return true;
    }

    public function forceDelete(): bool
    {
        if ($this->check('forceDelete')) {
            return true;
        }

        return true;
    }
}
