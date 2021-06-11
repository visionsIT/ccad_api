<?php

namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuantitySlot extends Model
{
	use SoftDeletes;
    protected $table = 'quantity_slots';
    protected $fillable = ['name','min_value','max_value','delivery_charges'];

    /**
    * @return \Illuminate\Database\Eloquent\Relations\hasMany
    */
}
