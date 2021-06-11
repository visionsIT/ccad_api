<?php namespace Modules\Reward\Http\Services;

use Illuminate\Support\Facades\Mail;
use Modules\Reward\Mails\OrderCancellation;
use Modules\Reward\Mails\OrderConfirmation;
use Modules\Reward\Mails\OrderShipping;
use Modules\Reward\Mails\OrderPlaced;
use Modules\Reward\Repositories\ProductOrderRepository;
use Modules\User\Models\UsersPoint;
use Modules\User\Models\ProgramUsers;
use Modules\Reward\Models\ProductOrder;
use Modules\Reward\Models\ProductDenomination;
use DB;
use Modules\User\Http\Services\UserNotificationService;
use Helper;

/**
 * Class CatalogueService
 *
 * @package Modules\Reward\Http\Services
 */
class ProductOrderService
{
    protected $repository;

    /**
     * ProductOrderService constructor.
     *
     * @param ProductOrderRepository $repository
     */
    public function __construct(ProductOrderRepository $repository,UserNotificationService $userNotificationService)
    {
        $this->repository = $repository;
        $this->notification_service = $userNotificationService;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function confirmOrder($id): bool
    {
        //$order = $this->repository->find($id);
        $order = ProductOrder::with(['product','product.currency'])->where('id',$id)->first();

        $actual_val = ProductDenomination::withTrashed()->select('value','price')->where('id',$order->denomination_id)->first();

        if($order->country_id != Null || $order->country_id != '' || $order->country_id != 'Null'){
            $currency = DB::table('countries')->select('currency_code as code')->where('id',$order->country_id)->first();
        }else{
            $currency = DB::table('currencies')->select('code')->where('id',$order->product->currency_id)->first();
        }
        
        $value = $currency->code.' '.$actual_val->value;
        $price = $currency->code.' '.$actual_val->price;

        if ($order->status !== 1) {
            return FALSE;
        }

        $this->repository->update([ 'status' => 2 ], $id);

        //Mail::send(new OrderConfirmation($order, $order->account, $order->product));
        
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'city'       => $order->city,
            'country'    => $order->country,
            'product_name'     => $order->product->name,
            'value'    => $value,
            'price'    => $price,
            'point'    => $order->value,
            'quantity' => $order->quantity,
			'delivery_charges' => $order->delivery_charges,
            'total_price' => $order->total_price,
            'order_number' => 'ccad-00'.$order->id,
        ];
        
        $emailcontent["template_type_id"] =  '4';
        $emailcontent["dynamic_code_value"] = array($data['username'],$data['product_name'],$data['value'],$data['city'],$data['country'],$data['price'],$data['quantity'],$data['point'],$data['delivery_charges'],$data['total_price']);
        $emailcontent["email_to"] = $data["email"];
        $emaildata = Helper::emailDynamicCodesReplace($emailcontent);

        $message = "<p>Hello ".$data['username'].",</p>";
        $message .= "<p>Your order has been confirmed.</p>";
        $message .= "<p><b>Product Name: </b>".$data['product_name']."</p>";
        $message .= "<p><b>Value: </b>".$data['value']."</p>";
        $message .= "<p><b>Price: </b>".$data['price']."</p>";
        $message .= "<p><b>Quantity: </b>".$data['quantity']."</p>";
        $message .= "<p><b>Points: </b>".$data['point']."</p>";
		$message .= "<p><b>Delivery Charges: </b>".$data['delivery_charges']."</p>";
        $message .= "<p><b>Total Points: </b>".$data['total_price']."</p>";
        $message .= "<p><b>State: </b>".$data['city']."</p>";
        $message .= "<p><b>Country: </b>".$data['country']."</p>";

        $saveNotification = $this->notification_service->creat_notification($order->account_id,Null,Null, $order->id, '4', $message);

        return TRUE;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function shipOrder($id): bool
    {
        //$order = $this->repository->find($id);
        $order = ProductOrder::with(['product','product.currency'])->where('id',$id)->first();

        $actual_val = ProductDenomination::withTrashed()->select('value','price')->where('id',$order->denomination_id)->first();
        if($order->country_id != Null || $order->country_id != '' || $order->country_id != 'Null'){
            $currency = DB::table('countries')->select('currency_code as code')->where('id',$order->country_id)->first();
        }else{
            $currency = DB::table('currencies')->select('code')->where('id',$order->product->currency_id)->first();
        }
        $value = $currency->code.' '.$actual_val->value;
        $price = $currency->code.' '.$actual_val->price;

        if ($order->status !== 2) {
            return FALSE;
        }

        $this->repository->update([ 'status' => 3 ], $id);

        
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'city'       => $order->city,
            'country'    => $order->country,
            'product_name'     => $order->product->name,
            'value'    => $value,
            'price'    => $price,
			'point'    => $order->value,
            'quantity' => $order->quantity,
			'delivery_charges' => $order->delivery_charges,
            'total_price' => $order->total_price,
            'order_number' => 'ccad-00'.$order->id,
        ];
        
        $emailcontent["template_type_id"] =  '5';
        $emailcontent["dynamic_code_value"] = array($data['username'],$data['product_name'],$data['value'],$data['city'],$data['country'],$data['price'],$data['quantity'],$data['point'],$data['delivery_charges'],$data['total_price']);
        $emailcontent["email_to"] = $data["email"];
        $emaildata = Helper::emailDynamicCodesReplace($emailcontent);

        //Mail::send(new OrderShipping($order, $order->account, $order->product));

        $message = "<p>Hello ".$data['username'].",</p>";
        $message .= "<p>Your order has been shipped.</p>";
        $message .= "<p><b>Product Name: </b>".$data['product_name']."</p>";
        $message .= "<p><b>Value: </b>".$data['value']."</p>";
        $message .= "<p><b>Price: </b>".$data['price']."</p>";
        $message .= "<p><b>Quantity: </b>".$data['quantity']."</p>";
		$message .= "<p><b>Points: </b>".$data['point']."</p>";
		$message .= "<p><b>Delivery Charges: </b>".$data['delivery_charges']."</p>";
        $message .= "<p><b>Total Points: </b>".$data['total_price']."</p>";
        $message .= "<p><b>State: </b>".$data['city']."</p>";
        $message .= "<p><b>Country: </b>".$data['country']."</p>";

        $saveNotification = $this->notification_service->creat_notification($order->account_id,Null,Null, $order->id, '3', $message);

        return TRUE;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function cancelOrder($id): bool
    {
        //$order = $this->repository->find($id);
        $order = ProductOrder::with(['product','product.currency'])->where('id',$id)->first();
        $actual_val = ProductDenomination::withTrashed()->select('value','price')->where('id',$order->denomination_id)->first();

        if($order->country_id != Null || $order->country_id != '' || $order->country_id != 'Null'){
            $currency = DB::table('countries')->select('currency_code as code')->where('id',$order->country_id)->first();
        }else{
            $currency = DB::table('currencies')->select('code')->where('id',$order->product->currency_id)->first();
        }
        $value = $currency->code.' '.$actual_val->value;
        $price = $currency->code.' '.$actual_val->price;

        if ($order->status === 3 || $order->status === -1) {
            return FALSE;
        }

        $this->repository->update([ 'status' => -1 ], $id);

        $program_user = ProgramUsers::where(['account_id'=>$order->account_id])->first();
        $user_points = UsersPoint::where([ 'user_id' => $program_user->id])->latest()->first();
        $old_points = $user_points->balance;
        $new_pts = (((int)$old_points)+((int)$order->value));
        UsersPoint::create([
                'value'    => $order->value,
                'product_order_id'=>$id,
                'user_id'    => $program_user->id,
                'transaction_type_id'    => 6,
                'description' => '',
                'balance'    => $new_pts,
                'created_by_id' => $user_points->created_by_id
            ]);

        
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'product_name'     => $order->product->name,
            'value'    => $value,
            'price'    => $price,
			'point'    => $order->value,
            'quantity' => $order->quantity,
			'delivery_charges' => $order->delivery_charges,
            'total_price' => $order->total_price,
            'order_number' => 'ccad-00'.$order->id,
        ];

        $emailcontent["template_type_id"] =  '6';
        $emailcontent["dynamic_code_value"] = array($data['username'],$data['product_name'],$data['value'],$data['price'],$data['quantity'],$data['point'],$data['delivery_charges'],$data['total_price']);
        $emailcontent["email_to"] = $data["email"];
        $emaildata = Helper::emailDynamicCodesReplace($emailcontent);

        //Mail::send(new OrderCancellation($order, $order->account, $order->product));

        $message = "<p>Hello ".$data['username'].",</p>";
        $message .= "<p>We have to cancel your order because the product is out of stock.</p>";
        $message .= "<p><b>Product Name: </b>".$data['product_name']."</p>";
        $message .= "<p><b>Value: </b>".$data['value']."</p>";
        $message .= "<p><b>Price: </b>".$data['price']."</p>";
        $message .= "<p><b>Quantity: </b>".$data['quantity']."</p>";
		$message .= "<p><b>Points: </b>".$data['point']."</p>";
		$message .= "<p><b>Delivery Charges: </b>".$data['delivery_charges']."</p>";
        $message .= "<p><b>Total Points: </b>".$data['total_price']."</p>";
	
        $saveNotification = $this->notification_service->creat_notification($order->account_id,Null,Null, $order->id, '2', $message);

        return TRUE;
    }

    /**
     * @return mixed
     */
    public function getPendingOrders()
    {
        return $this->repository->getPendingOrders();
    }

    /**
     * @return mixed
     */
    public function getConfirmedOrders()
    {
        return $this->repository->getConfirmedOrders();
    }

    /**
     * @return mixed
     */
    public function getCancelledOrders()
    {
        return $this->repository->getCancelledOrders();
    }

    /**
     * @return mixed
     */
    public function getShippedOrders()
    {
        return $this->repository->getShippedOrders();
    }

    public function filterOrders($data) {
        $record = $this->repository->getFilteredOrders($data);

        return $record;
    }

    public function placeOrder($id) {
        //$order = $this->repository->find($id);
        $order = ProductOrder::with(['product','product.currency'])->where('id',$id)->first();

        $actual_val = ProductDenomination::select('value','price')->where('id',$order->denomination_id)->first();
        if($order->country_id != Null || $order->country_id != '' || $order->country_id != 'Null'){
            $currency = DB::table('countries')->select('currency_code as code')->where('id',$order->country_id)->first();
        }else{
            $currency = DB::table('currencies')->select('code')->where('id',$order->product->currency_id)->first();
        }
        $value = $currency->code.' '.$actual_val->value;
        $price = $currency->code.' '.$actual_val->price;

        if ($order->status !== 1) {
            return FALSE;
        }

        // $this->repository->update([ 'status' => 2 ], $id);

        //Mail::send(new OrderConfirmation($order, $order->account, $order->product));
        
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'city'       => $order->city,
            'country'    => $order->country,
            'product_name'     => $order->product->name,
            'value'    => $value,
            'price'    => $price,
            'point'    => $order->value,
            'quantity' => $order->quantity,
            'delivery_charges' => $order->delivery_charges,
            'total_price' => $order->total_price,
            'order_number' => 'ccad-00'.$order->id,
        ];
        $emailcontent["template_type_id"] =  '3';
        $emailcontent["dynamic_code_value"] = array($data['username'],$data['product_name'],$data['value'],$data['city'],$data['country'],$data['price'],$data['quantity'],$data['point'],$data['delivery_charges'],$data['total_price']);
        $emailcontent["email_to"] = $data["email"];
        $emaildata = Helper::emailDynamicCodesReplace($emailcontent);

        $message = "<p>Hello ".$data['username'].",</p>";
        $message .= "<p>Your order has been placed.</p>";
        $message .= "<p><b>Product Name: </b>".$data['product_name']."</p>";
        $message .= "<p><b>Value: </b>".$data['value']."</p>";
        $message .= "<p><b>Price: </b>".$data['price']."</p>";
        $message .= "<p><b>Quantity: </b>".$data['quantity']."</p>";
        $message .= "<p><b>Points: </b>".$data['point']."</p>";
        $message .= "<p><b>Delivery Charges: </b>".$data['delivery_charges']."</p>";
        $message .= "<p><b>Total Points: </b>".$data['total_price']."</p>";
        $message .= "<p><b>State: </b>".$data['city']."</p>";
        $message .= "<p><b>Country: </b>".$data['country']."</p>";

        $saveNotification = $this->notification_service->creat_notification($order->account_id,Null,Null,$order->id, '1', $message);

        return TRUE;
    }

}
