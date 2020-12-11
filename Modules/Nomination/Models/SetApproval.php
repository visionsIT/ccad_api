<?php

namespace Modules\Nomination\Models;

use Spatie\Permission\Models\Role;
use Modules\User\Models\ProgramUsers;
use Modules\Account\Models\Permission;
use Illuminate\Database\Eloquent\Model;

class SetApproval extends Model
{
    protected $fillable = ['level_1_approval_type','level_1_permission','level_1_user','level_1_group','level_2_approval_type','level_2_permission','level_2_user','level_2_group','nomination_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function users_first_level()
    {
        return $this->hasOne(ProgramUsers::class, 'id', 'level_1_user');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function users_second_level()
    {
        return $this->hasOne(ProgramUsers::class, 'id', 'level_2_user');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permissions_first_level()
    {
        return $this->hasOne(Permission::class, 'id', 'level_1_permission');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function permissions_second_level()
    {
        return $this->hasOne(Permission::class,  'id','level_2_permission');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function groups_first_level()
    {
        return $this->hasOne(Role::class, 'id','level_1_group');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function groups_second_level()
    {
        return $this->hasOne(Role::class, 'id','level_2_group');
    }

    public function setLevel1UserAttribute($value)
    {
        $this->attributes['level_1_user'] = serialize($value);
    }

    public function setLevel2UserAttribute($value)
    {
        $this->attributes['level_2_user'] = serialize($value);
    }
}
