<?php

namespace DigitalCloud\PermissionTool\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function setPermissionsAttribute($value)
    {
        $this->syncPermissions($value);
    }
}
