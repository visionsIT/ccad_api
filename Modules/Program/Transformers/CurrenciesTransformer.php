<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Currency;

class CurrenciesTransformer extends TransformerAbstract
{
    /**
     * @param Currency $model
     *
     * @return array
     */
    public function transform(Currency $model): array
    {
        return [
            'id'   => $model->id,
            'name' => $model->name,
        ];
    }
}
