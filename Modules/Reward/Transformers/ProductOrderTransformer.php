<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\ProgramUsers;
use DB;
use Helper;

class ProductOrderTransformer extends TransformerAbstract
{
    /**
     * @param Catalogue $ProductOrder
     *
     * @return array
     */
    public function transform(ProductOrder $ProductOrder): array
    {
        $currentOrderStatus = 'Pending'; //status === 1 means pending
        if($ProductOrder->status === 3){
            $currentOrderStatus = 'Shipped';
        } elseif($ProductOrder->status === 2){
            $currentOrderStatus = 'Confirmed';
        } elseif($ProductOrder->status === -1){
            $currentOrderStatus = 'Cancelled';
        }
        $ProductOrderID = Helper::customCrypt($ProductOrder->id);
        $account_ID = Helper::customCrypt($ProductOrder->account_id);
        $user_data_program = ProgramUsers::where('account_id', $ProductOrder->account_id)->first()->toArray();
        $user_info = $user_data_program;
        unset($user_info['id']);
        unset($user_info['account_id']);
        $user_info['id'] = Helper::customCrypt($user_data_program['id']);
        $user_info['account_id'] = Helper::customCrypt($user_data_program['account_id']);
        $pointss = $ProductOrder->value / $ProductOrder->quantity;

        $pointss = $ProductOrder->value / $ProductOrder->quantity;

        return [
            'id'         => $ProductOrderID,
            'order_number'=> 'ccad-00'.$ProductOrder->id,
            'value'      => $ProductOrder->product->currency->code.' '.$ProductOrder->order_denomination->value,
            'denomination_value' =>$ProductOrder->order_denomination->value,
            'order_value'=> $ProductOrder->value,
            'quantity'   => $ProductOrder->quantity,
            'points'     => (string)$pointss,
            'product'    => optional($ProductOrder->product)->name,
            'image'      => optional($ProductOrder->product)->image,
            'name'       => optional($ProductOrder->account)->name,
            'account_id' => $account_ID,
            'user'       => $user_info,
            'first_name' => $ProductOrder->first_name,
            'last_name'  => $ProductOrder->last_name,
            'email'      => $ProductOrder->email,
            'phone'      => $ProductOrder->phone,
            'address'    => $ProductOrder->address,
            'city'       => ucfirst($ProductOrder->city),
            'comment'    => $ProductOrder->comment,
            'country'    => ucfirst($ProductOrder->country),
            'date'       => $ProductOrder->created_at,
            'status'     => $ProductOrder->status,
            'current_status'     => $currentOrderStatus,
            'is_gift'     => $ProductOrder->is_gift,
            'created_at'     => date('F j, Y g:i a', strtotime($ProductOrder->created_at)),
        ];
    }
}
