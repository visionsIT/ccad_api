<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\Departments;

class Teams extends Model
{
    protected $guarded = [];
    protected $fillable = [ 'dept_id', 'name' ];

    public function department()
    {
        return $this->hasOne(Departments::class, 'id','dept_id');
    }
}
