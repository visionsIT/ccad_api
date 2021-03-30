<?php

namespace Modules\User\Models;
use Modules\User\Models\UserNotifications;

use Illuminate\Database\Eloquent\Model;

class NotificationsType extends Model
{
    protected $fillable = ['name'];

    protected $table = "notifications_type";

    public function notifications()
    {
        return $this->hasMany(UserNotifications::class, 'id','notification_type_id');
    }
}
