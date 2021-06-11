<?php

namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RewardDeliveryCharge extends Model
{
	use SoftDeletes;
    protected $table = 'reward_delivery_charges';
    protected $fillable = ['catalog_id'];

    /**
    * @return \Illuminate\Database\Eloquent\Relations\hasMany
    */
}
