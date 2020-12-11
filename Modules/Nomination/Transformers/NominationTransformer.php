<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\Nomination;

class NominationTransformer extends TransformerAbstract
{
    /**
     * @param Nomination $model
     * @return array
     */
    public function transform(Nomination $model): array
    {
        return [
            'id'                    => $model->id,
            'name'                  => $model->name,
            'status'                => $model->status,
            'value_set'             => $model->value_set,
            'value_set_name'        => $model->value_set_relation->name,
            'value_set_relation'    => $model->value_set_relation,
            'multiple_recipient'    => $model->multiple_recipient,
            'approval_level'        => $model->approval_level,
            'reporting'             => $model->reporting,
        ];
    }

}
