<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\NominationDecline;

class NominationDeclineTransformer extends TransformerAbstract
{
    /**
     * @param NominationDecline $NominationDecline
     *
     * @return array
     */
    public function transform(NominationDecline $model): array
    {
        return [
            'id'                    => $model->id,
            'description'           => $model->description,
            'nomination_id'         => $model->nomination_id,

        ];
    }

}
