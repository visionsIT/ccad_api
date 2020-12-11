<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\ClaimType;

class ClaimTypeTransformer extends TransformerAbstract
{
    /**
     * @param ClaimType $NominationType
     *
     * @return array
     */
    public function transform(ClaimType $model): array
    {
        return [
            'id'                => $model->id,
            'name'              => $model->name,
            'active_url'        => $model->active_url,
            'not_active_url'    => $model->not_active_url,
            'points'            => $model->points,
        ];
    }

}
