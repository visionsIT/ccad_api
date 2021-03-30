<?php namespace Modules\Account\Http\Repositories;

use App\Repositories\Repository;
use Modules\Account\Models\Account;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\UsersPoint;
use Modules\Nomination\Models\UserNomination;
use DB;
use Modules\Reward\Models\Product;

class AccountRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = Account::class;

    /**
     * @param $email
     *
     * @return mixed
     */
    public function findAccountByEmail($email)
    {
        return $this->modeler->where('email', $email)->firstOrFail();
    }

    /**
     * @param Account $account
     * @param $data
     * @return mixed
     */
    public function syncPermissions(Account $account, $data)
    {
        return $account->syncPermissions($data['permission_names']);
    }

    public function getFilteredAccountData($data)
    {

        #type::0=>all,1=>debit,2=>credit
        //debit == receiver
        //credit = spent
        if ((!isset($data['from']) && !isset($data['to']) ) || ( ($data['from'] === '' || $data['from'] == null) && ($data['to'] === '' || $data['to'] == null)) ) {


            $p1 = DB::table('product_orders')
                ->where('product_orders.account_id', $data['account_id'])
                ->select('product_orders.id')
                ->addSelect(DB::raw("'order' as query_type"));

            $p2 = DB::table('user_nominations as sender')
                ->where('sender.account_id', $data['account_id'])
                ->select('sender.id')
                ->addSelect(DB::raw("'sender' as query_type"));

            $p3 = DB::table('user_nominations as receiver')
                ->where('receiver.user', $data['account_id'])
                ->select('receiver.id')
                ->addSelect(DB::raw("'receiver' as query_type"));

            if($data['type'] == '1'){
                #Debit=>receive
                $final = $p3;

            }else if($data['type'] == '2'){
                #credit=>spent
                $final = $p2->unionAll($p1);

            }else{
                $p = $p1->unionAll($p2);
                $final = $p->unionAll($p3);
            }    
            

            $accountNominations = DB::table(DB::raw("({$final->toSql()}) AS p"))
                ->mergeBindings($final)
                ->select('*')
                ->paginate(10);

             
            return $accountNominations;
        } else {
            $from = $data['from'] . ' 00:00:01';
            $to = $data['to'] . ' 23:59:59';

            $p1 = DB::table('product_orders')
                ->where('product_orders.account_id', $data['account_id'])
                ->whereBetween('product_orders.created_at', [$from, $to])
                ->select('product_orders.id')
                ->addSelect(DB::raw("'order' as query_type"));

            $p2 = DB::table('user_nominations as sender')
                ->where('sender.account_id', $data['account_id'])
                ->whereBetween('sender.created_at', [$from, $to])
                ->select('sender.id')
                ->addSelect(DB::raw("'sender' as query_type"));

            $p3 = DB::table('user_nominations as receiver')
                ->where('receiver.user', $data['account_id'])
                ->whereBetween('receiver.created_at', [$from, $to])
                ->select('receiver.id')
                ->addSelect(DB::raw("'receiver' as query_type"));

            if($data['type'] == '1'){
                #Debit=>receive
                $final = $p3;

            }else if($data['type'] == '2'){
                #credit=>spent
                $final = $p2->unionAll($p1);

            }else{
                $final = $p1->unionAll($p2, $p3);
            }   

            $accountNominations = DB::table(DB::raw("({$final->toSql()}) AS p"))
                ->mergeBindings($final)
                ->select('*')
                ->paginate(10);

            return $accountNominations;
        }
    }


}
