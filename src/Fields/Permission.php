<?php

namespace DigitalCloud\PermissionTool\Fields;

use DigitalCloud\PermissionTool\Models\Role;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Permission extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'permission-field';

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The name of the Eloquent "morph to many" relationship.
     *
     * @var string
     */
    public $manyToManyRelationship;

    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);
        $this->options();
    }

    public function options()
    {
        return $this->withMeta([
            'options' => \DigitalCloud\PermissionTool\Models\Permission::get()->map(function ($permission) {
                $permissionDisplay = substr($permission->name, 0, strrpos($permission->name, '-'));
                $action = !in_array($permissionDisplay, config('permission.permissions.resource'))
                    && !in_array($permissionDisplay, collect(config('permission.permissions.custom_permissions'))->flatten()->toArray())
                    && !str_contains($permissionDisplay, '(readonly)')
                    && !str_contains($permissionDisplay, '(hidden)');

                $field = str_contains($permissionDisplay, '(readonly)')
                && str_contains($permissionDisplay, '(hidden)');
                $fieldType = null;

                if ($classIndex = strrpos($permissionDisplay, '\\')) {
                    $permissionDisplay = substr($permissionDisplay, $classIndex + 1);
                }
                $permissionDisplay = Str::replace('_', ' ', Str::title(Str::snake($permissionDisplay)));
                $resourceClass = substr($permission->name, strrpos($permission->name, '-') + 1);

                if ($resourceClass == 'Laravel\Nova\Tool') {
                    $group = 'Tools';
                    $action = false;
                } else if ($resourceClass == 'Laravel\Nova\Dashboard') {
                    $group = 'Dashbaords';
                    $action = false;
                } else if ($resourceClass == 'CustomPermission') {
                    $group = 'Custom Permission';
                    $action = false;
                } else if (str_contains($permissionDisplay, '(Readonly)') ) {
                    $permissionDisplay = str_replace('(Readonly)','',$permissionDisplay);
                    $group = $resourceClass::label();
                    $action = false;
                    $field = true;
                    $fieldType = 'readonly';
                } else if (str_contains($permissionDisplay, '(Hidden)') ) {
                    $permissionDisplay = str_replace('(Hidden)','',$permissionDisplay);
                    $group = $resourceClass::label();
                    $action = false;
                    $field = true;
                    $fieldType = 'hidden';
                } else {
                    // Normal Resource Permission
                    $group = $resourceClass::label();
                    $field = false;
                }

                return [
                    'display' => $permissionDisplay,
                    'value' => $permission->id,
                    'resource' => $group,
                    'action' => $action,
                    'field' => $field,
                    'fieldType' => $fieldType
                ];
            })->values()->all(),
        ]);
    }

    public function checked($checked = [])
    {
        return $this->withMeta(['checked' => $checked]);
    }

    private function shouldSaveAsString()
    {
        return (array_key_exists('save_as_string', $this->meta)
            && $this->meta['save_as_string']
        );
    }

    private function shouldSaveUnchecked()
    {
        return (array_key_exists('save_unchecked', $this->meta)
            && $this->meta['save_unchecked']
        );
    }

    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {

            $data = json_decode($request[$requestAttribute]);

            if ($this->shouldSaveAsString()) {
                $value = implode(',', $this->onlyChecked($data));
            } elseif ($this->shouldSaveUnchecked()) {
                $value = $data;
            } else {
                $value = $this->onlyChecked($data);
            }

            $model->{$attribute} = $value;
        }
    }

    public function resolveAttribute($resource, $attribute = null)
    {
        $value = data_get($resource, $attribute);

        if (!$value) return json_encode($this->withUnchecked([]));

        if (is_array($value)) {
            if ($this->arrayIsAssoc($value)) {
                $all = $this->withUnchecked([]);
                $mer = array_merge($all, $value);
                return json_encode($mer);
            }
            return json_encode($this->withUnchecked($value));
        }

        return json_encode($this->withUnchecked($value->toArray()));
    }

    private function withUnchecked($data)
    {
        return collect($this->meta['options'])
            ->mapWithKeys(function ($option) use ($data) {
                $value = array_filter($data, function ($item) use ($option) {
                    return $option['value'] == $item['id'];
                });
                $isChecked = $value ? true : false;
                return [$option['value'] => $isChecked];
            })
            ->all();
    }

    private function onlyChecked($data)
    {

        return collect($data)
            ->filter(function ($isChecked) {
                return $isChecked;
            })
            ->map(function ($value, $key) {
                return $key;
            })
            ->values()
            ->all();
    }

    private function arrayIsAssoc(array $array)
    {
        if ([] === $array) return false;

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
