<?php

namespace DigitalCloud\PermissionTool\Resources\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Contracts\Role;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\PermissionRegistrar;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait AuthorizedQueries
{
    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if (!$request->isResourceIndexRequest()) {
            return $query;
        }

        // Do not apply on excluded resources
        $resource = $request->resource();
        if (in_array($resource, config('permission.permissions.exclude_resources', []))) {
            return $query;
        }

        // Allow view on all records
        $permission = sprintf('viewAny-%s', $resource);
        if (Gate::check($permission)) {
            return $query;
        }

        // Is User Model.
        // Requires check against id field
        if (get_class($query->getModel()) == config('permission.models.user')) {
            return $query->where('id', request()->user()->id);
        }

        // Record belongsTo or is AssignedTo User
        if ($resource::$model === $query->getModel()::class && method_exists($query->getModel(), 'assignees') ) {
            return $query->where(function ($q) {
                $q->whereHas('assignees', function ($q) {
                    $q->where('users.id', request()->user()->id);
                })->orWhere(config('permission.column_names.user_id'), request()->user()->id);
            });
        }

        return $query->where(config('permission.column_names.user_id'), request()->user()->id);
    }
}
