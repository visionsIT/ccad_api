<?php namespace Modules\Reward\Repositories;

use App\Repositories\Repository;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\UsersPoint;
use DB;


class ProductOrderRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = ProductOrder::class;


    /**
     * @param $token
     * @param $type || type 0 for password resets &&  type 1 for activation link
     *
     * @return mixed
     */
    public function UserOrders($account_id)
    {
        return ProductOrder::where('account_id', $account_id)->get();
    }

    public function getPendingOrders()
    {
        return $this->modeler->where('status' , 1)->paginate(20);
    }

    public function getConfirmedOrders()
    {
        return $this->modeler->where('status' , 2)->paginate(20);
    }

    public function getCancelledOrders()
    {
        return $this->modeler->where('status' , -1)->paginate(20);
    }

    public function getShippedOrders()
    {
        return $this->modeler->where('status' , 3)->paginate(20);
    }

    public function getFilteredOrders($data)
    {
        if (($data['from'] === '' || $data['from'] == null) && ($data['to'] === '' || $data['to'] == null)) {
            $awardPoints = UsersPoint::select(DB::Raw('SUM(value) as awarded_points'))->where('value', '>', 0)->first();

            $totalNumberOfOrders = ProductOrder::select(DB::Raw('SUM(value) as points_redeemed'), DB::Raw('COUNT(product_id) as order_count'), 'product_id')
            ->groupBy('product_id')
            ->get();

            return response()->json([ 'totalNumberOfOrders' => $totalNumberOfOrders, 'awardedPoints' => $awardPoints->awarded_points ]);
        } else {
            $from = $data['from'];
            $to = $data['to'] . ' 23:59:00';

            $totalNumberOfOrders = ProductOrder::select(DB::Raw('SUM(value) as points_redeemed'), DB::Raw('COUNT(product_id) as order_count'), 'product_id')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('product_id')
            ->get();

            $awardPoints = UsersPoint::select(DB::Raw('SUM(value) as awarded_points'))->where('value', '>', 0)->whereBetween('created_at', [$from, $to])->first();

            return response()->json([ 'totalNumberOfOrders' => $totalNumberOfOrders, 'awardedPoints' => $awardPoints->awarded_points ]);
        }
    }
}
