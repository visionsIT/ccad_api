<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\ProductCatalog;

class ProductCatalogTransformer extends TransformerAbstract
{
    /**
     * @param Catalogue $ProductCatalog
     * @return array
     */
    public function transform(ProductCatalog $ProductCatalog): array
    {
        return [
            'id'   => $ProductCatalog->id,
            'name' => $ProductCatalog->name,
            'status' => $ProductCatalog->status,
            'sub_categories' => $ProductCatalog->subCategories()->select('id', 'name', 'status')->get()->all(),
        ];
    }
}
