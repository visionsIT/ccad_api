<?php

namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCatalog extends Model
{
    protected $fillable = ['name', 'status'];

    protected $dates = ['deleted_at'];

    /**
    * @return \Illuminate\Database\Eloquent\Relations\hasMany
    */
    public function subCategories()
    {
        return $this->hasMany(ProductCategory::class, 'catalog');
    }
}
