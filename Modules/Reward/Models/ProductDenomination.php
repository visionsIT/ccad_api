<?php

namespace Modules\Reward\Models;


use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProductDenomination extends Model
{

    use  SoftDeletes, Presentable;

    protected $presenter = ProgramPresenter::class;

    protected $fillable = [ 'value', 'points', 'product_id', 'country_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}

