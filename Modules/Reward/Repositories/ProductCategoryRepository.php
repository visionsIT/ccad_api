<?php namespace Modules\Reward\Repositories;

use App\Repositories\Repository;
use Modules\Reward\Models\ProductCategory;

class ProductCategoryRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = ProductCategory::class;

    public function MainCategory()
    {
        return ProductCategory::where('parent',null)->get();
    }


}
