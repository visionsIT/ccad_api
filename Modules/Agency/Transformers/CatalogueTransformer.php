<?php namespace Modules\Agency\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Agency\Models\Catalogue;

class CatalogueTransformer extends TransformerAbstract
{
    /**
     * @param Catalogue $catalogue
     * @return array
     */
    public function transform(Catalogue $catalogue): array
    {
        return [
            'id'   => $catalogue->id,
            'name' => $catalogue->name,
        ];
    }
}
