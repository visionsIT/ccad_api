<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\AwardsLevel;

class AwardsLevelTransformer extends TransformerAbstract
{
    /**
     * @param AwardsLevel $model
     * @return array
     */
    public function transform(AwardsLevel $model): array
    {
        return [
            'id'                    => $model->id,
            'name'                  => $model->name,
            'description'           => $model->description,
            'points'                => $model->points,
            'nomination_type'       => $model->nomination_type,
            'status'                => $model->status,
        ];
    }

}
