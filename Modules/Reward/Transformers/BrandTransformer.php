<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\Product;

class BrandTransformer extends TransformerAbstract
{
        /**
     * @param ue $product
     *
     * @return array
     */
    public function transform(Product $product): array
    {
        return [
            'category_id'   => $product->catalog_id,
            'category_name' => $product->catalog->name,
            'brand_id'      => $product->brand->id,
            'brand_name'    => $product->brand->name,
        ];
    }

}
