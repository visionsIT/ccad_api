<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\UsersGoalItem;

class GoalItemTransformer extends TransformerAbstract
{
    /**
     * @param UsersGoalItem $model
     *
     * @return array
     */
    public function transform(UsersGoalItem $model): array
    {
        $data =  [
            'id'         => $model->id,
            'product'    => $model->product,
            'user'       => $model->user,
            'created_at' => $model->created_at,
        ];
        $data['product']['denominations'] = $model->product->denominations()->select('id', 'value', 'points')->orderBy('value','ASC')->get()->all();
        return $data;
    }
}
