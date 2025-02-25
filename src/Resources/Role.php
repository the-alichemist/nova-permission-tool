<?php

namespace DigitalCloud\PermissionTool\Resources;

use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use App\Rules\HasPermission;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\BelongsToMany;
use Fourstacks\NovaCheckboxes\Checkboxes;
use Spatie\Permission\PermissionRegistrar;
use DigitalCloud\CheckboxList\CheckboxList;
use Laravel\Nova\Http\Requests\NovaRequest;
use DigitalCloud\PermissionTool\PermissionTool;

class Role extends Resource
{
    protected $rolePermissions = [];

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \DigitalCloud\PermissionTool\Models\Role::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    public static $displayInNavigation = true;

    public static function getModel()
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    public static function label()
    {
        return __('PermissionTool::resources.Roles');
    }

    public static function singularLabel()
    {
        return __('PermissionTool::resources.Role');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\NovaRequest\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $this->setupPermission($request);
        $userResource = Nova::resourceForModel(getModelForGuard($this->guard_name));

        $fields =  [
            ID::make()->sortable(),

            Text::make(__('PermissionTool::roles.name'), 'name')
                ->rules(['required', 'string', 'max:255'])
                ->creationRules('unique:' . config('permission.table_names.roles'))
                ->updateRules('unique:' . config('permission.table_names.roles') . ',name,{{resourceId}}'),

            \DigitalCloud\PermissionTool\Fields\Permission::make(__('PermissionTool::resources.Permissions'), 'permissions')->onlyOnForms(),

            Text::make('permissions count')->withMeta(['value' => count($this->permissions)])->exceptOnForms(),
            // Text::make('users count')->withMeta(['value' => count($this->users)])->exceptOnForms(),
            // DateTime::make(__('PermissionTool::roles.created_at'), 'created_at')->exceptOnForms(),
            // DateTime::make(__('PermissionTool::roles.updated_at'), 'updated_at')->exceptOnForms(),

            // BelongsToMany::make(__('PermissionTool::resources.Permissions'), 'permissions', Permission::class),
            // MorphToMany::make($userResource::label(), 'users', $userResource),


        ];

        return $fields;
    }

    public function setupPermission(NovaRequest $request)
    {
        $this->setupResourcePermissions($request);
        $this->setupToolPermissions();
        $this->setupCustomPermissions();
        $this->syncPermissions();
    }

    protected function setupResourcePermissions(NovaRequest $request)
    {
        $resourcePermissions = config('permission.permissions.resource');
        foreach (Nova::$resources as $resource) {
            if ($resource == 'Laravel\Nova\Actions\ActionResource') {
                continue;
            }

            if (in_array($resource, config('permission.permissions.exclude_resources'))) {
                continue;
            }
            // $resourceName = strtolower(substr(strrchr($resource, "\\"), 1));
            // $resourceName = $resource;

            foreach ($resourcePermissions as $permission) {
                $value = "$permission-$resource";
                $this->rolePermissions[] = $value;
            }

            // add resource actions
            $object = new $resource($resource::$model);
            foreach ($object->actions($request) as $action) {
                if ($action->name) {
                    $name = $action->name . "-$resource";
                } else {
                    $name = get_class($action) . "-$resource";
                }
                $this->rolePermissions[] = $name;
            }
        }
    }

    protected function setupToolPermissions()
    {
        $tools = collect(Nova::$tools)->filter(function ($tool) {
            return $tool->menu(request()) && !in_array(get_class($tool), [
                'Laravel\Nova\Tools\Dashboard',
                'Laravel\Nova\Tools\ResourceManager',
                "DigitalCloud\PermissionTool\PermissionTool"
            ]);
        })->toArray();
        foreach ($tools as $tool) {
            $this->rolePermissions[] = PermissionTool::getToolPermission($tool);
        }
    }


    protected function setupCustomPermissions()
    {
        $permissions = config('permission.permissions.custom_permissions', []);

        foreach ($permissions as $key => $permission) {
            if (is_array($permission)) {
                foreach ($permission as $p) {
                    $this->rolePermissions[] = sprintf('%s-%s', $p, $key);
                }
            } else {
                $this->rolePermissions[] = sprintf('%s-CustomPermission', $permission);
            }
        }
    }

    protected function syncPermissions()
    {
        foreach (collect($this->rolePermissions)->unique()->toArray() as $resourcePermission) {
            \DigitalCloud\PermissionTool\Models\Permission::firstOrCreate(
                ['name' => $resourcePermission],
                ['guard_name' => 'web']
            );
        }
        \DigitalCloud\PermissionTool\Models\Permission::whereNotIn('name', $this->rolePermissions)->delete();
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\NovaRequest\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\NovaRequest\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\NovaRequest\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }


    //    public function authorizedToDelete(Request $request)
    //    {
    //        return false;
    //    }
    //
    //    public function authorizedToUpdate(Request $request)
    //    {
    //        return ($this->id == 1)? false : true;
    //    }
}
