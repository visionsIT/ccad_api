<?php

namespace Modules\Reward\Models;


use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProductBrand  extends Model
{
    protected $fillable = ['name'];

    protected $dates = ['deleted_at'];
}

