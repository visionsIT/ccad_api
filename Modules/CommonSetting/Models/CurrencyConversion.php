<?php

namespace Modules\CommonSetting\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyConversion extends Model
{
    protected $table = "currency_conversion"; 
    protected $fillable = [ 'from_currency','to_currency', 'conversion', 'status' ];
}
