<?php

namespace DigitalCloud\PermissionTool\Resources\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Contracts\Role;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\PermissionRegistrar;
use Laravel\Nova\Contracts\RelatableField;
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
        $fieldClass = $request->newResource()
                    ->availableFields($request)
                    ->whereInstanceOf(RelatableField::class)
                    ->findFieldByAttribute($request->field, function () {
                        abort(404);
                    })->resourceClass;
        $permission = sprintf('viewAny-%s', $fieldClass);

        if (Gate::check($permission)) {
            return $query;
        }
        return $query->where(config('permission.column_names.user_id'), request()->user()->id);
    }
}

