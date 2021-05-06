<?php

namespace Modules\CommonSetting\Models;

use Illuminate\Database\Eloquent\Model;

class PointRateSettings extends Model
{

	protected $table = "point_rate_settings"; 
    protected $fillable = [ 'country_id','currency_id', 'points', 'status' ];

    public function currency()
    {
        return $this->belongsTo('Modules\Program\Models\Currency', 'currency_id');
    }

    public function country()
    {
        return $this->belongsTo('Modules\Program\Models\Countries', 'country_id');
    }
}
