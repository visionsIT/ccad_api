<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\NominationType;

class UserBadgesTransformer extends TransformerAbstract
{
    /**
     * @param NominationType $model
     * @return array
     */
    public function transform(NominationType $model): array
    {
        return [
            'name'  => $model->name,
            'icon' => $model->account_id?$model->active_url:$model->not_active_url,
            'earned' => $model->account_id?true:false,
            'status'  => $model->status,
            //'active_url' => $model->active_url,
          //  'not_active_url' => $model->not_active_url,
        ];
    }

}
