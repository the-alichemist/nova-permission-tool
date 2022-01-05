<?php

namespace DigitalCloud\PermissionTool;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use DigitalCloud\PermissionTool\Resources\Role;
use DigitalCloud\PermissionTool\Resources\Permission;
use DigitalCloud\PermissionTool\Policies\AbstractPolicy;

class PermissionTool extends Tool
{
    public $roleResource = Role::class;
    public $permissionResource = Permission::class;

    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::resources([
            $this->roleResource,
            $this->permissionResource,
        ]);

        Nova::script('PermissionTool', __DIR__ . '/../dist/js/tool.js');
        Nova::style('PermissionTool', __DIR__ . '/../dist/css/tool.css');

        $this->registerPolicies();
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        return view('PermissionTool::navigation');
    }

    public function roleResource(string $roleResource)
    {
        $this->roleResource = $roleResource;

        return $this;
    }

    public function permissionResource(string $permissionResource)
    {
        $this->permissionResource = $permissionResource;

        return $this;
    }

    public function registerPolicies()
    {
        $abstractPolicy = AbstractPolicy::class;;
        $resources = Nova::$resources;

        foreach ($resources as $resource) {
            if ($resource == 'Laravel\Nova\Actions\ActionResource') {
                continue;
            }
            $anonymousPolicy = eval("return (new class extends $abstractPolicy {
                public \$resource = '$resource';
            });");
            Gate::policy($resource::$model, get_class($anonymousPolicy));
        }
    }

    /**
     * @param String $tool
     * @return String
     */
    public static function getToolPermission($tool)
    {
        return sprintf('%s-Laravel\Nova\Tool', get_class($tool));
    }

    public static function register()
    {
        Nova::serving(function (ServingNova $event) {
            $tools = collect(Nova::$tools)->filter(function ($tool) {
                return $tool->renderNavigation() && !in_array(get_class($tool), [
                    // Laravel Nova Offical Resources
                    'Laravel\Nova\Tools\Dashboard',
                    'Laravel\Nova\Tools\ResourceManager',
                    // -----END----
                    "DigitalCloud\PermissionTool\PermissionTool"
                ]);
            });
            $tools->each(function ($tool) {
                $tool->canSee(function () use ($tool) {
                    return Gate::check(static::getToolPermission($tool));
                });
            });
        });
    }
}
