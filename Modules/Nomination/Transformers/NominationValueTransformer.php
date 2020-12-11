<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\NominationValue;

class NominationValueTransformer extends TransformerAbstract
{
    /**
     * @param NominationValue $NominationValue
     *
     * @return array
     */
    public function transform(NominationValue $model): array
    {
        dd($model);
        return [
            'id'                    => $model->id,
            'name'                  => $model->name,
            'description'           => $model->description,
            'logo'                  => $model->logo,
            'featured'              => $model->featured,
            'value_set'             => $model->value_set,

        ];
    }

}
