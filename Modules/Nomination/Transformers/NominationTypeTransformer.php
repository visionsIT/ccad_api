<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\NominationType;

class NominationTypeTransformer extends TransformerAbstract
{
    /**
     * @param NominationType $NominationType
     *
     * @return array
     */
    public function transform(NominationType $model): array
    {
        return [
            'id'                    => $model->id,
            'name'                  => $model->name,
            'description'           => $model->description,
            'logo'                  => $model->logo,
            'featured'              => $model->featured,
            'value_set'             => $model->value_set,
            'points'             => $model->points,
            'status'                => $model->status,
            'levels'                => $model->awards_level->count(),
            'levels_list'           => $model->awards_level,
            'active_url'            => $model->active_url,
            'not_active_url'        => $model->not_active_url,
        ];
    }

}
