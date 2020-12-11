<?php namespace Modules\Reward\Http\Services;

use Illuminate\Support\Facades\Mail;
use Modules\Reward\Mails\OrderCancellation;
use Modules\Reward\Mails\OrderConfirmation;
use Modules\Reward\Mails\OrderShipping;
use Modules\Reward\Mails\OrderPlaced;
use Modules\Reward\Repositories\ProductOrderRepository;

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
    public function __construct(ProductOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function confirmOrder($id): bool
    {
        $order = $this->repository->find($id);

        if ($order->status !== 1) {
            return FALSE;
        }

        $this->repository->update([ 'status' => 2 ], $id);

        //Mail::send(new OrderConfirmation($order, $order->account, $order->product));
        $image_url = [
            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        ];
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'city'       => $order->city,
            'country'    => $order->country,
            'product_name'     => $order->product->name,
            'value'    => $order->value,
        ];
        Mail::send('emails.orderConfirmation', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
            $m->to($data["email"])->subject('Order Confirmation!');
        });

        return TRUE;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function shipOrder($id): bool
    {
        $order = $this->repository->find($id);

        if ($order->status !== 2) {
            return FALSE;
        }

        $this->repository->update([ 'status' => 3 ], $id);

        $image_url = [
            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        ];
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'city'       => $order->city,
            'country'    => $order->country,
            'product_name'     => $order->product->name,
            'value'    => $order->value,
        ];
        Mail::send('emails.orderShipped', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
            $m->to($data["email"])->subject('Order Shipment!');
        });

        //Mail::send(new OrderShipping($order, $order->account, $order->product));

        return TRUE;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function cancelOrder($id): bool
    {
        $order = $this->repository->find($id);

        if ($order->status === 3 || $order->status === -1) {
            return FALSE;
        }

        $this->repository->update([ 'status' => -1 ], $id);

        $image_url = [
            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        ];
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'product_name'     => $order->product->name,
            'value'    => $order->value,
        ];
        Mail::send('emails.orderCancellation', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
            $m->to($data["email"])->subject('Order Cancellation!');
        });

        //Mail::send(new OrderCancellation($order, $order->account, $order->product));

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
        $order = $this->repository->find($id);

        if ($order->status !== 1) {
            return FALSE;
        }

        // $this->repository->update([ 'status' => 2 ], $id);

        //Mail::send(new OrderConfirmation($order, $order->account, $order->product));
        $image_url = [
            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        ];
        $data = [
            'email' => $order->email,
            'username' => $order->first_name.' '. $order->last_name,
            'city'       => $order->city,
            'country'    => $order->country,
            'product_name'     => $order->product->name,
            'value'    => $order->value,
        ];
        Mail::send('emails.orderPlaced', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
            $m->to($data["email"])->subject('Order Placed!');
        });

        return TRUE;
    }

}
