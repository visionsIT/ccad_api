<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\UsersGoalItem;
use Helper;
use DB;

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

        $denomination = $model->product->denominations()->select('id', 'value')->orderBy('value','ASC')->get()->toArray();

        $product_country = DB::table('point_rate_settings')->where('default_currency','1')->first();
        if(empty($product_country)){
            $user_country = DB::table('program_users')->select('country_id')->where('id',$model->user_id)->first();

            if(!empty($user_country)){
                $product_country = DB::table('point_rate_settings')->where('country_id',$user_country->country_id)->first();
            }
        }

        

        if(isset($product_country) && !empty($product_country)){
            $points = $product_country->points;
        }
        else{
            $points = '10';
        }

        $points = trim($points);

        

        $denomination_all = $denomination;
        foreach ($denomination_all as $key1 => $answer) {
            unset($denomination_all[$key1]['id']);
        }
        
        foreach ($denomination as $key => $value) {

            if(is_numeric($value['value']) && is_numeric($points)){
                $denomination_all[$key]['points']  = round(trim($value['value'])*$points,2);
                $denomination_all[$key]['id'] = Helper::customCrypt($value['id']);
            }

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
