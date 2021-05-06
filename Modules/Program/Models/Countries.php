<?php

namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    protected $fillable = [ 'name','code','currency_name','currency_code' ];
}
