<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    protected $guarded = [];
    protected $fillable = [ 'name' ];
}
