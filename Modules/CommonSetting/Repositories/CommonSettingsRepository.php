<?php namespace Modules\CommonSetting\Repositories;

use Carbon\Carbon;
use App\Repositories\Repository;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Nomination\Models\ValueSet;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersGroupList;
use Modules\User\Models\PageVisits;
use Modules\Account\Models\Account;
use Modules\User\Models\UserCampaignsBudget;
use DateTime;
use DB; 
use Modules\CommonSetting\Models\PointRateSettings;
use Modules\Nomination\Models\UserNomination;
use Modules\Reward\Models\ProductOrder;

class CommonSettingsRepository extends Repository
{

    protected $modeler = CampaignSettings::class;

    /**
     * @param Get Ripple Setting Data
     * @return mixed
     */

    public function getCampaignName($campaign_id){
        $campaign_name = ValueSet::select('name')->where('id',$campaign_id)->first();
        return $campaign_name->name;
    }

    public function getAccountCurrencyPoints($country_id){
        $currency_points = PointRateSettings::select('points')->where('country_id',$country_id)->first();
        return $currency_points->points;
    }
    
    public function getUserGroups($accountid)
    {

        return $allGroupsOfUser = UsersGroupList::join('roles as t1', "t1.id","=","users_group_list.user_group_id")->join('user_roles as t2', "t2.id","=","users_group_list.user_role_id")
            ->where('users_group_list.account_id',$accountid)
            ->where('users_group_list.status','1')
            ->select("users_group_list.user_group_id","t1.name as group_name","users_group_list.user_role_id","t2.name as role_name")
            ->orderBy('users_group_list.user_role_id','ASC') // Get Lowest role Group Ic
            ->get()->toArray();
    }

    /*************************
    get active/ inactive users
    **************************/
    public function getActiveInActiveUsers($total_registrations, $data){
        $users = array();
        $activeUser = [];
        $inactiveUser = [];
        for($i = 0; $i < $total_registrations; $i++) {
            $account_id = $data[$i]->id;
            $user_login = Account::select('last_login')->where('id',$account_id)->first();
            if($user_login->last_login != ''){
                $last_login = date('Y-m-d', strtotime($user_login->last_login));
                $last_login = new DateTime(date($last_login));
                $today = new DateTime(date("Y-m-d"));
                $diff = $today->diff($last_login)->format("%a");

                if($diff <= 3){
                    array_push($activeUser, $data[$i]);
                }else{
                    array_push($inactiveUser, $data[$i]);
                }
            }else{
                array_push($inactiveUser, $data[$i]);
            }
        }
        $users['count_active_users'] = count($activeUser);
        $users['active_users'] = $activeUser;
        $users['count_inactive_users'] = count($inactiveUser);
        $users['inactive_users'] = $inactiveUser;

        return $users;
    }/******fn_ends*****/

    /***************************
    get website visit users data
    ***************************/
    public function getTotalWebsiteVisitedCount($accounts,$from,$to){
        $account_id = array();
        //$main_array = array();
        if(!empty($accounts)){
            foreach($accounts as $key=>$account){
                $account_id[] = $account['id'];
            }
        }

        // /whereIn('account_id',$account_id)->
        $website_visit_count = PageVisits::whereIn('account_id',$account_id)->where('page_name','login')->whereBetween('created_at', [$from, $to])->sum('visits_count');
        return (int)$website_visit_count;

    }#fn_ends

    /***************************
    get website visit users data
    ***************************/
    public function getActiveUserVisitedCount($accounts,$from,$to){
        $account_id = array();
        //$main_array = array();
        if(!empty($accounts)){
            foreach($accounts as $key=>$account){
                $account_id[] = $account['account_id'];
            }
        }
        
        $website_visit_count = PageVisits::whereIn('account_id',$account_id)->where('page_name','login')->whereBetween('created_at', [$from, $to])->sum('visits_count');
        return $website_visit_count;

    }#fn_ends

    /**********
    avg visit
    **********/
    public function getAvgVisitPerActiveUser($count_active,$count_visits){

        if($count_active == 0){
            return 0;
        }else{
            return round($count_visits/$count_active , 2);
        }
        
    }#fn_ends

    public function getCommonNominationQuery($campaign_id,$group_id,$from, $to){
        $nomination_data = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to]);

        if($campaign_id != '0'){
            $nomination_data = $nomination_data->where('campaign_id',$campaign_id);
        }
        $awarded_data = $nomination_data->where(function($q){
                            $q->where(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '1');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '1');
                            });
                        });
        return $awarded_data;
    }

    public function getCommonPendingNominationQuery($campaign_id,$group_id,$from, $to){
        $nomination_data = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to]);

        if($campaign_id != '0'){
            $nomination_data = $nomination_data->where('campaign_id',$campaign_id);
        }
        $pending_data = $nomination_data->where(function($q){
                            $q->where(function($query){
                                $query->where('level_1_approval', '0')
                                ->where('level_2_approval', '0');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '0');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '0')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '0');
                            });
                        });
        return $pending_data;
    }

    public function getFinalAllDatesNominations($nomination_approved_graphData,$pdf_filter,$from,$to,$year_for_week){
        //echo "<pre>";print_r($nomination_approved_graphData);die;
        $campaign_colors = $this->getCampaignColors();
        $new_array = array();
        foreach($nomination_approved_graphData as $key=>$value){
            // get missing dates in list -- code start here
            
            if(isset($pdf_filter) && $pdf_filter == 0){

                $startTime = strtotime($from);
                $endTime = strtotime($to);
                $days = array();
                while ($startTime < $endTime) {  
                    $days[] = date('d M Y', $startTime); 
                    $startTime += strtotime('+1 days', 0);
                }

                $nomination_point_label=array();
                $nomination_point_count=array();
                foreach($days as $day){
                    $dd = date('d M Y', strtotime($day));
                    if(isset($value['nomination_point_label'])){
                        if (!in_array($dd, $value['nomination_point_label'])){
                            $nomination_point_label[] = $dd;
                            $nomination_point_count[] = 0;
                        } else {
                            $indexNumber = array_search($dd, $value['nomination_point_label']);
                            $nomination_point_label[] = $value['nomination_point_label'][$indexNumber];
                            $nomination_point_count[] = $value['nomination_point_count'][$indexNumber];
                        }
                    }else{
                        $nomination_point_label[] = $dd;
                        $nomination_point_count[] = 0;
                    }
                    
                }
                /*$date1=date_create($from);
                $date2=date_create($to);
                $diff=date_diff($date1,$date2);
                $daysCount = $diff->format("%a");  
                $nomination_point_label=array();
                $nomination_point_count=array();

                for ($i=0; $i <= $daysCount; $i++) {  
                    $dd = date('d M', strtotime($from));

                    if(isset($value['nomination_point_label'])){
                        if (!in_array($dd, $value['nomination_point_label'])){
                            $nomination_point_label[] = $dd;
                            $nomination_point_count[] = 0;
                        }
                    }else{
                        $nomination_point_label[] = $dd;
                        $nomination_point_count[] = 0;
                    }
                    
                    $from = date('Y-m-d', strtotime($from. ' + 1 days'));
                }*/

                $value['nomination_point_label'] = $nomination_point_label;
                $value['nomination_point_count'] = $nomination_point_count;
                $value['color'] = $campaign_colors[$key];
                //$value['color'] = '#'.substr(md5(rand()), 0, 6);
                
                
            }else if(isset($pdf_filter) && $pdf_filter == 1){
                
                $startTime = strtotime($from);
                $endTime = strtotime($to);
                $weeks = array();
                while ($startTime < $endTime) {  
                    $weeks[] = date('W', $startTime); 
                    $startTime += strtotime('+1 week', 0);
                }
                //echo "<pre>";print_r($weeks);die;
                $nomination_point_label=array();
                $nomination_point_count=array();
                foreach($weeks as $week){
                    $week_date = $this->getStartAndEndDate($week,$year_for_week);
                    if(isset($value['nomination_point_label'])){
                        if (!in_array($week_date, $value['nomination_point_label'])){
                            $nomination_point_label[] = $week_date;
                            $nomination_point_count[] = 0;
                        } else {
                            $indexNumber = array_search($week_date, $value['nomination_point_label']);
                            $nomination_point_label[] = $value['nomination_point_label'][$indexNumber];
                            $nomination_point_count[] = $value['nomination_point_count'][$indexNumber];
                        }
                    }else{
                        $nomination_point_label[] = $week_date;
                        $nomination_point_count[] = 0;
                    }
                    
                }
                
                $value['nomination_point_label'] = $nomination_point_label;
                $value['nomination_point_count'] = $nomination_point_count;
                $value['color'] = $campaign_colors[$key];
                //$value['color'] = '#'.substr(md5(rand()), 0, 6);
                
            }else if(isset($pdf_filter) && $pdf_filter == 2){

                $period = \Carbon\CarbonPeriod::create($from, '1 month', $to);
                $nomination_point_label=array();
                $nomination_point_count=array();
                foreach ($period as $dt) {
                    $month_date = Carbon::createFromFormat('m Y', $dt->format("m Y"))->format('M Y');
                    if(isset($value['nomination_point_label'])){
                        if (!in_array($month_date, $value['nomination_point_label'])){
                            $nomination_point_label[] = $month_date;
                            $nomination_point_count[] = 0;
                        } else {
                            $indexNumber = array_search($month_date, $value['nomination_point_label']);
                            $nomination_point_label[] = $value['nomination_point_label'][$indexNumber];
                            $nomination_point_count[] = $value['nomination_point_count'][$indexNumber];
                        }
                    }else{
                        $nomination_point_label[] = $month_date;
                        $nomination_point_count[] = 0;
                    }
                    
                }
                
                $value['nomination_point_label'] = $nomination_point_label;
                $value['nomination_point_count'] = $nomination_point_count;
                $value['color'] = $campaign_colors[$key];
                //$value['color'] = '#'.substr(md5(rand()), 0, 6);
                
            }
            // get missing dates in list -- code end here

            $new_array[$key] = $value;
        }
        return $new_array;
    }

    /*******fn to get select week dates*******/
    public function getStartAndEndDate($week, $year) {
        $dto = new DateTime();
        $ret = $dto->setISODate($year, $week)->format('d M Y').'-'.$dto->modify('+6 days')->format('d M Y');
        return $ret;
    }


    /***************************
    common function to get order
    placed by given account ids
    ***************************/
    public function getOrderPlacedQuery($account_id,$from,$to,$user_country){
        $orders = ProductOrder::join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to]);
        return $orders;
    }/****fn_ends****/

    public function getOrderShippedQuery($account_id,$from,$to,$user_country){
        $orders = ProductOrder::join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to])->where('product_orders.status','3');
        return $orders;
    }/*****fn_ends***/

    /*****************************
    fn to get all dates of shipped
    orders count and points sum
    ******************************/
    public function getFinalGraphData($data_graph,$pdf_filter,$from,$to,$year_for_week){
        // get missing dates in list -- code start here
        if(isset($pdf_filter) && $pdf_filter == 0){
            $date1=date_create($from);
            $date2=date_create($to);
            $diff=date_diff($date1,$date2);
            $daysCount = $diff->format("%a");
            $data_label=array();
            $data_count=array();

            for ($i=0; $i <= $daysCount; $i++) {
                $dd = date('d M Y', strtotime($from));

                if(isset($data_graph['data_label'])){
                    if (!in_array($dd, $data_graph['data_label'])){
                        $data_label[] = $dd;
                        $data_count[] = 0;
                    } else {
                        $indexNumber = array_search($dd, $data_graph['data_label']);
                        $data_label[] = $data_graph['data_label'][$indexNumber];
                        $data_count[] = $data_graph['data_count'][$indexNumber];
                    }
                }else{
                    $data_label[] = $dd;
                    $data_count[] = 0;
                }

                $from = date('Y-m-d', strtotime($from. ' + 1 days'));
            }
            $data_graph['data_label'] = $data_label;
            $data_graph['data_count'] = $data_count;

        }else if(isset($pdf_filter) && $pdf_filter == 1){

            $startTime = strtotime($from);
            $endTime = strtotime($to);
            $weeks = array();
            while ($startTime < $endTime) {
                $weeks[] = date('W', $startTime);
                $startTime += strtotime('+1 week', 0);
            }
            //echo "<pre>";print_r($weeks);die;
            $data_label=array();
            $data_count=array();
            foreach($weeks as $week){
                $week_date = $this->getStartAndEndDate($week,$year_for_week);
                if(isset($data_graph['data_label'])){
                    if (!in_array($week_date, $data_graph['data_label'])){
                        $data_label[] = $week_date;
                        $data_count[] = 0;
                    } else {
                        $indexNumber = array_search($week_date, $data_graph['data_label']);
                        $data_label[] = $data_graph['data_label'][$indexNumber];
                        $data_count[] = $data_graph['data_count'][$indexNumber];
                    }
                }else{
                    $data_label[] = $week_date;
                    $data_count[] = 0;
                }

            }
            $data_graph['data_label'] = $data_label;
            $data_graph['data_count'] = $data_count;


        }else if(isset($pdf_filter) && $pdf_filter == 2){

            $period = \Carbon\CarbonPeriod::create($from, '1 month', $to);
            $data_label=array();
            $data_count=array();
            foreach ($period as $dt) {
                $month_date = Carbon::createFromFormat('m Y', $dt->format("m Y"))->format('M Y');
                if(isset($data_graph['data_label'])){
                    if (!in_array($month_date, $data_graph['data_label'])){
                        $data_label[] = $month_date;
                        $data_count[] = 0;
                    } else {
                        $indexNumber = array_search($month_date, $data_graph['data_label']);
                        $data_label[] = $data_graph['data_label'][$indexNumber];
                        $data_count[] = $data_graph['data_count'][$indexNumber];
                    }
                }else{
                    $data_label[] = $month_date;
                    $data_count[] = 0;
                }

            }
            $data_graph['data_label'] = $data_label;
            $data_graph['data_count'] = $data_count;

        }
        // get missing dates in list -- code end here

        return $data_graph;
    }
    /***************************
    Function to get Most Popular
    Rewards
    ***************************/
    public function getMostPopularRewards($from, $to, $country_id) {
        // $popularRewards = DB::table('products')
        // ->join('product_orders', 'product_orders.product_id', '=', 'products.id')
        // ->join('products_accounts_seen', 'products_accounts_seen.product_id', '=', 'product_orders.product_id')
        // ->join('product_categories', 'product_categories.id', '=', 'products.category_id')
        // ->select('products.id', 'products.name as product_name', 'product_categories.name as category_name', DB::Raw('COUNT(product_orders.id) as order_count'), DB::Raw('COUNT(products_accounts_seen.id) as order_viewed'))
        // ->where('product_orders.status', '=', 3)
        // ->where('product_orders.created_at', '>=',$from)
        // ->where('product_orders.created_at', '<=',$to)
        // ->groupBy('products.id')
        // ->get();

        $popularRewards = DB::table('products as p')
            ->select('p.id as productid', 'p.name as product_name','c.name as category_name', DB::Raw('COUNT(t.id) as order_viewed'),'o.order_count')
            ->join(DB::raw('(SELECT o.product_id,o.created_at, count(distinct o.id) as order_count FROM product_orders as o GROUP BY o.product_id) as o'),'p.id','o.product_id')
            ->leftjoin('products_accounts_seen as t', 'p.id', '=', 't.product_id')
            ->leftjoin('products_countries as x', 'p.id', '=', 'x.product_id')
            ->join('product_categories as c', 'c.id', '=', 'p.category_id')
            //->where('product_orders.status', '=', 3)
            ->where('o.created_at', '>=',$from)
            ->where('o.created_at', '<=',$to)
            ->where('x.country_id',$country_id)
            ->groupBy('p.id','o.order_count')
            ->orderBy('o.order_count','desc')
            ->get();

        $allProdCount = DB::table('products')->count();
        $ordered = DB::table('product_orders')->where('status',3)->count();
        $totalseen = DB::table('products_accounts_seen')->distinct()->count('product_id');

        $seenPercentage = ($totalseen / $allProdCount)*100;
        $orderedPercentage = ($ordered / $allProdCount)*100;
        $neverSeen = $allProdCount - $totalseen;
        $neverseenPercentage = ($neverSeen / $allProdCount)*100;

        $finalData['data'] = $popularRewards;
        $finalData['label'] = 'Most popular rewards';
        $finalData['chartData'] = array(
            'seenPercentage' => number_format($seenPercentage,1),
            'orderPercentage' => number_format($orderedPercentage,1),
            'neverSeenPercentage' => number_format($neverseenPercentage,1)
        );

        return $finalData;
    }

    /************************
    get colors for campaigns
    ************************/
    public function getCampaignColors(){

        $colors = array('#E6B0AA','#C39BD3','#A9CCE3','#A2D9CE','#FAD7A0','#E59866','#CCD1D1','#922B21','#1F618D','#138D75','#B7950B','#E67E22','#34495E','#0E6251','#40E0D0','#DE3163','#FA8072','#CCCCFF','#FF0000','#00FF00','#0000FF','#800080','#808000','#808080','#000000','#999999','#FAD7A0');

        $campaign_colors = array();
        $campaign_names = ValueSet::select('id','name')->get();
        if(!empty($campaign_names)){
            foreach($campaign_names as $key=>$value){
                if (array_key_exists($key,$colors)){
                    $campaign_colors[$value->name] = $colors[$key];
                }else{
                    $campaign_colors[$value->name] = '#784212';
                }
            }
        }
        return $campaign_colors;
    }/*********fn_ends_here********/
    
}
