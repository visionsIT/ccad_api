<?php namespace Modules\Reward\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\ProgramUsers;
use DB;

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

        $pointss = $ProductOrder->value / $ProductOrder->quantity;

        return [
            'id'         => $ProductOrder->id,
            'order_number'=> 'ccad-00'.$ProductOrder->id,
            'value'      => $ProductOrder->product->currency->code.' '.$ProductOrder->denomination->value,
            'denomination_value' =>$ProductOrder->denomination->value,
            'order_value'=> $ProductOrder->value,
            'quantity'   => $ProductOrder->quantity,
            'points'     => (string)$pointss,
            'product'    => optional($ProductOrder->product)->name,
            'image'      => optional($ProductOrder->product)->image,
            'name'       => optional($ProductOrder->account)->name,
            'account_id' => $ProductOrder->account_id,
            'user'       => ProgramUsers::where('account_id', $ProductOrder->account_id)->first(),
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
