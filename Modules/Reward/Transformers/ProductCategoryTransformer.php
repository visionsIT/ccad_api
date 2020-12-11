<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\ProductCategory;

class ProductCategoryTransformer extends TransformerAbstract
{
    /**
     * @param Categoryue $ProductCategory
     * @return array
     */
    public function transform(ProductCategory $model): array
    {
        return [
            'id'   => $model->id,
            'name' => $model->name,
            'status' => $model->status,
            'catalog_id' => $model->catalog,
            'catalog_name' => $model->catalogs->name,
            'Sub' => $model->sub()->select('id','name')->get()->all(),

        ];
    }
}
