<?php namespace Modules\User\Repositories;

use App\Repositories\Repository;
use Modules\User\Models\UsersPoint;
use DB;

class PointRepository extends Repository
{
    protected $modeler = UsersPoint::class;

    /**
     * @param $user_id
     * @param $data
     *
     * @return mixed
     */
    public function updateOrCreate($user_id, $data)
    {
        return $this->modeler->updateOrCreate([ 'user_id' => $user_id ], $data);
    }

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function aggregateBalance($user_id)
    {
        return $this->modeler->where('user_id', $user_id)->sum('value');
    }

    /**
     * @param $user_id
     *
     * @return mixed
     */
    public function aggregateUserBalance($user_id)
    {
        return $this->modeler->where('user_id', $user_id)->sum('value');
    }

    public function filter($data, $pagination_count)
    {
        $query = (new $this->modeler)->query();

        if (isset($data['transaction_type_id'])) {
            $query->where('transaction_type_id', $data['transaction_type_id']);
        }

        if (isset($data['from_date'])) {
            $query->whereDate('from', '<=', $data['from_date']);
        }

        if (isset($data['to_date'])) {
            $query->whereDate('from', '>=', $data['to_date']);
        }

        return $query->get();

    }

    public function filterPointsHistory($data)
    {
        switch ($data['type']) {
            case 'Instant':
              if ($data['fromDate'] != '' && $data['toDate'] != '') {
                $from = $data['fromDate'];
                $to = $data['toDate'] . ' 23:59:00';

                $data = UsersPoint::whereBetween('created_at', [$from, $to])->where('transaction_type_id', 7)->where('user_nominations_id', NULL)->where('product_order_id', NULL)->paginate(20);
              } else {
                $data = UsersPoint::where('transaction_type_id', 7)->where('user_nominations_id', NULL)->where('product_order_id', NULL)->paginate(20);
              }
              break;
            case 'Nomination':
                if ($data['fromDate'] != '' && $data['toDate'] != '') {
                  $from = $data['fromDate'];
                  $to = $data['toDate'] . ' 23:59:00';

                  $data = UsersPoint::whereBetween('created_at', [$from, $to])->where('transaction_type_id', 6)->where('user_nominations_id', '!=', NULL)->where('product_order_id', NULL)->paginate(20);
                } else {
                  $data = UsersPoint::where('transaction_type_id', 6)->where('user_nominations_id', '!=', NULL)->where('product_order_id', NULL)->paginate(20);
                }
              break;
            case 'Project':
                if ($data['fromDate'] != '' && $data['toDate'] != '') {
                  $from = $data['fromDate'];
                  $to = $data['toDate'] . ' 23:59:00';

                  $data = UsersPoint::join('user_nominations', 'users_points.user_nominations_id', 'user_nominations.id')->whereBetween('users_points.created_at', [$from, $to])->where('users_points.transaction_type_id', 6)->where('users_points.user_nominations_id', '!=', NULL)->where('users_points.product_order_id', NULL)->paginate(20);
                } else {
                  $data = UsersPoint::where('users_points.transaction_type_id', 6)->where('users_points.user_nominations_id', '!=', NULL)->where('users_points.product_order_id', NULL)->join('user_nominations', 'users_points.user_nominations_id', 'user_nominations.id')->where('user_nominations.team_nomination', 1)->paginate(20);

                  // $data = DB::select( DB::raw("select * from `users_points`") )->where('users_points.transaction_type_id', 6)->where('users_points.user_nominations_id1', '!=', NULL)->where('users_points.product_order_id', NULL)->join('user_nominations', 'users_points.user_nominations_id', 'user_nominations.id')->where('user_nominations.team_nomination', 1)->paginate(20);
                }
              break;
            case 'Order':
                if ($data['fromDate'] != '' && $data['toDate'] != '') {
                  $from = $data['fromDate'];
                  $to = $data['toDate'] . '23:59:00';

                  $data = UsersPoint::whereBetween('created_at', [$from, $to])->where('transaction_type_id', 6)->where('user_nominations_id', NULL)->where('product_order_id', '!=', NULL)->paginate(20);
                } else {
                  $data = UsersPoint::where('transaction_type_id', 6)->where('user_nominations_id', NULL)->where('product_order_id', '!=', NULL)->paginate(20);
                }
              break;
            default:
            if ($data['fromDate'] != '' && $data['toDate'] != '') {
              $from = $data['fromDate'];
              $to = $data['toDate'] . ' 23:59:00';
              $data = UsersPoint::whereBetween('created_at', [$from, $to])->orderBy('created_at', 'desc')->paginate(20);
            } else {
              $data = UsersPoint::orderBy('created_at', 'desc')->paginate(20);
            }
        }

//        echo "<pre>"; print_r($data); die;
        return $data;
    }
}
