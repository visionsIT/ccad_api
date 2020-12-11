<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class ValueSetSettings extends Model
{
    protected $table = 'value_set_settings';
    protected $fillable = [ 'value_set_id', 'sender_all_users', 'sender_user_permissions', 'sender_user_permissions_L1', 'sender_user_permissions_L2', 'sender_user_groups', 'sender_user_ids', 'sender_group_ids', 'receiver_users', 'receiver_group_ids' ];
}
