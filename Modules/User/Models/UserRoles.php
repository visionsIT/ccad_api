<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    protected $table = 'user_roles';
    protected $fillable = [ 'name', 'status' ];
}
