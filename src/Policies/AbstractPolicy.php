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
        // return Nova::resourceForKey(request()->route('resource'));
        return $this->resource;
    }
    
    public function check($permission, $recordOwnership = false)
    {
        $resource = $this->resourceClass();
        $permission = sprintf('%s-%s', $permission, $resource);

        if (!$recordOwnership) {
            if (Gate::check($permission)) {
                return true;
            }
        } else {
            $record = $resource::$model::findOrFail(request()->route('resourceId'));
            $userIdCol = config('permission.column_names.user_id');
            $hasUser = array_key_exists($userIdCol, $record->getAttributes());

            if (Gate::check($permission)) {
                if (!$hasUser) {
                    return true;
                } else if ($record->$userIdCol == request()->user()->id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function viewAny(): bool
    {
        if ($this->check('view') || $this->check('viewAny')) {
            return true;
        }

        return false;
    }

    public function view(): bool
    {
        if ($this->check('viewAny') || $this->check('view', true)) {
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

    public function update(): bool
    {
        if ($this->check('updateAny') || $this->check('update', true)) {
            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        if ($this->check('deleteAny') || $this->check('delete', true)) {
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
