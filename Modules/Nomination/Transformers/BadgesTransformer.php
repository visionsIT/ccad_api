<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\NominationType;

class BadgesTransformer extends TransformerAbstract
{
    /**
     * @param NominationType $model
     * @return array
     */
    public function transform(NominationType $model): array
    {
        return [
            'times'      => $model->times,
            'active_url' => $model->active_url,
            'not_active' => $model->not_active_url
        ];
    }

}
