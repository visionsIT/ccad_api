<?php namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Reward\Presenters\ProductCategoryPresenter;
use Modules\Reward\Models\ProductCatalog;


class ProductCategory extends Model
{
    use Presentable;

    protected $presenter = ProductCategoryPresenter::class;

    protected $fillable = ['name','parent','catalog','status'];

    protected $dates = ['deleted_at'];



    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prod_parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function catalogs(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCatalog::class, 'catalog');
    }



    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sub()
    {
        return $this->hasMany(ProductCategory::class,'parent');
    }

}
