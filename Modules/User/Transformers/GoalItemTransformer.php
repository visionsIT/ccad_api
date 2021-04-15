<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\UsersGoalItem;
use Helper;

class GoalItemTransformer extends TransformerAbstract
{
    /**
     * @param UsersGoalItem $model
     *
     * @return array
     */
    public function transform(UsersGoalItem $model): array
    {

        $goal_item = Helper::customCrypt($model->id);
        $product = $model->product;
        $product = $product->toArray();
        $product_info = $product;

        unset($product_info['id']);
        $product_info['id'] = Helper::customCrypt($product['id']);

        $denomination = $model->product->denominations()->select('id', 'value', 'points')->orderBy('value','ASC')->get()->toArray();

        $denomination_all = $denomination;
        foreach ($denomination_all as $key1 => $answer) {
            unset($denomination_all[$key1]['id']);
        }
        
        foreach ($denomination as $key => $value) {
            $denomination_all[$key]['id'] = Helper::customCrypt($value['id']);
        }

        $data =  [
            'id'         => $goal_item,
            'product'    => $product_info,
            'user'       => $model->user,
            'created_at' => $model->created_at,
        ];
        $data['product']['denominations'] = $denomination_all;
        return $data;
    }
}
