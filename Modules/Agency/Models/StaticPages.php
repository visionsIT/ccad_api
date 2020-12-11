<?php

namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPages extends Model
{
    protected $fillable = ['title', 'allies_name', 'page_type', 'description', 'status'];
}
