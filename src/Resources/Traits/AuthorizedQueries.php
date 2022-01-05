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
            return;
        }

        // if ($query->getModel()->getTable() != (new $resource::$model)->getTable()) {
        //     return;
        // }

        $resource = $request->resource();;
        $permission = sprintf('viewAny-%s', $resource);

        if (Gate::check($permission)) {
            return $query;
        }

        // User Model should search with id, not user_id
        if (get_class($query->getModel()) == config('permission.models.user')) {
            return $query->where('id', request()->user()->id);
        }

        return $query->where(config('permission.column_names.user_id'), request()->user()->id);
    }
}
