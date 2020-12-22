<?php

namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsCountries extends Model
{
    protected $fillable = ['id','product_id','country_id'];
}
