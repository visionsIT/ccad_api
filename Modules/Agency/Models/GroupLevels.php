<?php

namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;

class GroupLevels extends Model
{
    protected $fillable = ['name', 'description', 'status'];
}
