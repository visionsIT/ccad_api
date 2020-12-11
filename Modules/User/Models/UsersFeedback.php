<?php


namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;


class UsersFeedback extends Model
{
    protected $fillable = [ 'user_id', 'email', 'feedback', 'created_at', 'updated_at' ];
}
