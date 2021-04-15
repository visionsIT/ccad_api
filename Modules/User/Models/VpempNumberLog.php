<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class VpempNumberLog extends Model
{
    protected $table = 'user_vp_emp_log';
    protected $fillable = [ 'user_account_id', 'previous_vp_emp', 'new_vp_emp_number' ];
}
