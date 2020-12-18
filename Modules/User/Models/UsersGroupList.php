<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;
use Modules\User\Models\ProgramUsers;
use Spatie\Permission\Models\Role;

class UsersGroupList extends Model
{
	protected $table = 'users_group_list';
    protected $fillable = [ 'account_id', 'user_group_id', 'user_role_id' ];

     /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function group()
    {
        return $this->belongsTo(Role::class, 'user_group_id');
    }

    public function programUserData() {
        return $this->belongsTo(ProgramUsers::class, 'account_id','account_id');
    }
}
