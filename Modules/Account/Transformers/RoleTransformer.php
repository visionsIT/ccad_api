<?php namespace Modules\Account\Transformers;

use Spatie\Permission\Models\Role;
use Modules\Agency\Models\GroupLevels;
use League\Fractal\TransformerAbstract;
use Modules\User\Models\UsersGroupList;

class RoleTransformer extends TransformerAbstract
{
    /**
     * @param Role $role
     * @return array
     */
    public function transform(Role $role): array
    {
        return [
            'id'   =>  $role->id,
            'name' => $role->name,
            'program_id' => $role->program_id,
            'children' => Role::where('parent_id', $role->id)->get(),
            'is_default' => $role->is_default,
            'permissions' => $role->permissions,
            'permissions_count' => $role->permissions()->count(),
            //'users_cont' => $role->users()->count(),
            'users_cont' => UsersGroupList::where('user_group_id', $role->id)->count(),
            'group_level_id' => GroupLevels::where('id', $role->group_level_id)->first(),
            'group_level_parent_id' => $role->group_level_parent_id?GroupLevels::where('id', $role->group_level_parent_id)->first():'',
            'nomination_approval_access' => $role->nomination_approval_access,
            'instant_point_access' => $role->instant_point_access,
            'project_compaign_access' => $role->project_compaign_access,
            'general_permission' => $role->general_permission,
            'rewards_module_permission' => $role->rewards_module_permission,
            'birthday_campaign_permission' => $role->birthday_campaign_permission,
            'birthday_message' => $role->birthday_message,
            'birthday_points' => $role->birthday_points,
            'status' => $role->status,
        ];
    }
}
