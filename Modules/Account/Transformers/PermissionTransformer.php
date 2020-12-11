<?php namespace Modules\Account\Transformers;

use Modules\Account\Models\Permission;
use League\Fractal\TransformerAbstract;

class PermissionTransformer extends TransformerAbstract
{
    /**
     * @param Permission $permission
     * @return array
     */
    public function transform(Permission $permission): array
    {
        return [
            'id'   =>  $permission->id,
            'name' => $permission->name,
            'value' => $permission->value,
            'table_name' => $permission->table_name,
            'description' => $permission->description,
        ];
    }
}
