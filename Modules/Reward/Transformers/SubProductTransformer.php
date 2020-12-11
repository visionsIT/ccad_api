<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\SubProduct;

class SubProductTransformer extends TransformerAbstract
{
    /**
     * @param ue $SubProduct
     * @return array
     */
    public function transform(SubProduct $SubProduct): array
    {
        return [
            'id'   => $SubProduct->id,
            'name' => $SubProduct->name,
            'product' => $SubProduct->product->name,
        ];
    }
}
