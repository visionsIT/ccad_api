<?php namespace Modules\CommonSetting\Http\Services;

use Carbon\Carbon;
use Modules\CommonSetting\Repositories\CommonSettingsRepository;
use Modules\Account\Models\Account;
use Modules\User\Models\UsersGroupList;
use DateTime;
use Modules\User\Models\ProgramUsers;
use DB;
use Modules\User\Models\PageVisits;
use Modules\Nomination\Models\UserNomination;
use Modules\Nomination\Models\ValueSet;
use Modules\Reward\Models\ProductOrder;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductsAccountsSeen;
/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class CommonService
{
    protected $common_repository;

    /**
     * PasswordsService constructor.
     *
     * @param AccountRepository $account_repository
     */
    public function __construct(CommonSettingsRepository $common_repository)
    {
        $this->common_repository = $common_repository;
    }

    /*******Overall REport*****/
    public function filterUserOverallReport($data,$group_id) {
        $final_array = array();
        $data_pdf_filter = $data['pdf_filter'];
        if(isset($data['pdf_filter']) && $data['pdf_filter'] != ''){
            $pdf_filter = $data['pdf_filter'];
        }else{
            $pdf_filter = '0';
        }
        if($pdf_filter == '1'){
            $pdf_filter = "week";
        }else if($pdf_filter == '2'){
            $pdf_filter = "month";
        }else{
            $pdf_filter = "date";
        }

        if ((!isset($data['from']) && !isset($data['to']) ) || ( ($data['from'] === '' || $data['from'] == null) && ($data['to'] === '' || $data['to'] == null)) ) {

            if(isset($data['month']) && $data['month'] != ''){//if_month_selected
                $query_date = date("Y").'-'.$data['month'].'-01';
                $from = date('Y-m-01', strtotime($query_date));// First day of the month.
                $to = date('Y-m-t', strtotime($query_date));// Last day of the month.
            }else{ //no_filter
                $from = Null;
                $to = Null;
            }
        }else{//date
            $from = $data['from'] . ' 00:00:01';
            $to = $data['to'] . ' 23:59:59';
        }

        $overview_data = UsersGroupList::select('account_id')->whereIn('user_group_id',$group_id)->whereBetween('created_at', [$from, $to])->distinct()->get();

        $account_id = array();
        if(!empty($overview_data)){
            foreach($overview_data as $key=>$account){
                $account_id[] = $account['account_id'];
            }
        }

        $total_register = Account::whereIn('id',$account_id)->whereBetween('created_at', [$from, $to])->get();

        if($pdf_filter == 'month'){
            $registered_data_pdf_filter = Account::select(DB::raw("COUNT( 'created_at' ) AS entries"), DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))->whereIn('id',$account_id)->whereBetween('created_at', [$from, $to])->groupBy('monthyear')->get();
        }else{
            $registered_data_pdf_filter = Account::select(DB::raw("COUNT( 'created_at' ) AS entries"), DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.''))->whereIn('id',$account_id)->whereBetween('created_at', [$from, $to])->groupBy($pdf_filter)->get();
        }

        $year_for_week = date('Y', strtotime($from));

        #get_website_visit_data_for_graph
        //whereIn('account_id',$account_id)#commented_bcs_we_get_data_of_from_to_dates
        if($pdf_filter == 'month'){
             $visit_data = PageVisits::select([DB::raw("SUM(visits_count) as total_visit"), DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear")])->where('page_name','login')->whereBetween('created_at', [$from, $to])->groupBy('monthyear')->get();

            $visit_data_unique = PageVisits::select([DB::raw("Count(visits_count) as total_visit"), DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear")])->where('page_name','login')->where('is_unique','1')->whereBetween('created_at', [$from, $to])->groupBy('monthyear')->get();
        }else{

            $visit_data = PageVisits::select([DB::raw("SUM(visits_count) as total_visit"), DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.'')])->where('page_name','login')->whereBetween('created_at', [$from, $to])->groupBy($pdf_filter)->get();

            $visit_data_unique = PageVisits::select([DB::raw("Count(visits_count) as total_visit"), DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.'')])->where('page_name','login')->where('is_unique','1')->whereBetween('created_at', [$from, $to])->groupBy($pdf_filter)->get();
        }
        $website_visit_data = array();
        if(!$visit_data->isEmpty()){
            foreach($visit_data as $key=>$visitData){
                if(isset($visitData['date'])){
                    $website_visit_data['data_label'][] = date('d M Y', strtotime($visitData['date']));
                }else if(isset($visitData['week'])){
                    $website_visit_data['data_label'][] = $this->common_repository->getStartAndEndDate($visitData['week'],$year_for_week);
                }else{
                    $website_visit_data['data_label'][] = Carbon::createFromFormat('m-Y', $visitData['monthyear'])->format('M Y');
                }

                $website_visit_data['data_count'][] = (int)$visitData['total_visit'];
                $website_visit_data['label'] = 'Website Visits';
            }
        }
        if( (isset($website_visit_data['data_label']) && isset($website_visit_data['data_count']))){
            $website_visit_data = $this->common_repository->getFinalGraphData($website_visit_data,$data_pdf_filter,$from,$to,$year_for_week);
        }


        $website_visit_unique_data = array();
        if(!$visit_data_unique->isEmpty()){
            foreach($visit_data_unique as $key=>$visitDataUnique){
                if(isset($visitDataUnique['date'])){
                    $website_visit_unique_data['data_label'][] = date('d M Y', strtotime($visitDataUnique['date']));
                }else if(isset($visitDataUnique['week'])){
                    $website_visit_unique_data['data_label'][] = $this->common_repository->getStartAndEndDate($visitDataUnique['week'],$year_for_week);
                }else{
                    $website_visit_unique_data['data_label'][] = Carbon::createFromFormat('m-Y', $visitDataUnique['monthyear'])->format('M Y');
                }

                $website_visit_unique_data['data_count'][] = $visitDataUnique['total_visit'];
            }
        }
        if( (isset($website_visit_unique_data['data_label']) && isset($website_visit_unique_data['data_count']))){
            $website_visit_unique_data = $this->common_repository->getFinalGraphData($website_visit_unique_data,$data_pdf_filter,$from,$to,$year_for_week);
        }


        $website_visit['website_visit_data'] = $website_visit_data;
        $website_visit['website_visit_unique_data'] = $website_visit_unique_data;

        $final_array['total_registrations'] = count($total_register);

        $users = $this->common_repository->getActiveInActiveUsers($final_array['total_registrations'], $total_register);

        $website_visits = $this->common_repository->getTotalWebsiteVisitedCount($total_register,$from,$to);

        $active_user_visits = $this->common_repository->getTotalWebsiteVisitedCount($users['active_users'],$from,$to);
        $avg_visit = $this->common_repository->getAvgVisitPerActiveUser($users['count_active_users'],$active_user_visits);

        $registered_data_graph = array();
        if(!$registered_data_pdf_filter->isEmpty()){
            foreach($registered_data_pdf_filter as $key=>$register_data){
                if(isset($register_data['date'])){
                    $registered_data_graph['data_label'][] = date('d M Y', strtotime($register_data['date']));
                }else if(isset($register_data['week'])){
                    $registered_data_graph['data_label'][] = $this->common_repository->getStartAndEndDate($register_data['week'],$year_for_week);
                }else{
                    $registered_data_graph['data_label'][] = Carbon::createFromFormat('m-Y', $register_data['monthyear'])->format('M Y');
                }

                $registered_data_graph['data_count'][] = $register_data['entries'];
            }
        }

        if( (isset($registered_data_graph['data_label']) && isset($registered_data_graph['data_count']))){
            $registered_data_graph = $this->common_repository->getFinalGraphData($registered_data_graph,$data_pdf_filter,$from,$to,$year_for_week);
        }

        $registered_data_graph['label'] = 'Registration';
        $final_array['total_active_users'] = $users['count_active_users'];
        $final_array['total_inactive_users'] = $users['count_inactive_users'];
        $final_array['total_website_visits'] = $website_visits;
        $final_array['registered_data_graph'] = $registered_data_graph;
        $final_array['avg_active_user_visits'] = $avg_visit;
        $final_array['website_visit_graph'] = $website_visit;


        return $final_array;
    }



    /************************
    fn for recognition report
    *************************/
    public function filterUserRecognitionReport($data,$group_id){
        $final_array = array();
        $campaign_id = $data['campaign_id'];
        $data_pdf_filter = $data['pdf_filter'];

        if(isset($data['pdf_filter']) && $data['pdf_filter'] != ''){
            $pdf_filter = $data['pdf_filter'];
        }else{
            $pdf_filter = '0';
        }
        if($pdf_filter == '1'){
            $pdf_filter = "week";
        }else if($pdf_filter == '2'){
            $pdf_filter = "month";
        }else{
            $pdf_filter = "date";
        }

        $currency_id = '1';
        $currency_code = 'AED';
        $currency_points = $this->common_repository->getAccountCurrencyPoints($currency_id);


        $from = $data['from'] . ' 00:00:01';
        $to = $data['to'] . ' 23:59:59';
        $year_for_week = date('Y', strtotime($from));

        $nomination_query = $this->common_repository->getCommonNominationQuery($campaign_id,$group_id,$from, $to);

        $awards_issued = $nomination_query->count();
        $coins_awarded = $nomination_query->selectRaw('SUM(points) AS total_count')->orderBy('created_at','ASC')->first();

        $pending_nomination_query = $this->common_repository->getCommonPendingNominationQuery($campaign_id,$group_id,$from, $to);
        $pending_data = $pending_nomination_query->selectRaw('SUM(points) AS total_count')->orderBy('created_at','ASC')->first();

        $pending_nominationQuery = $this->common_repository->getCommonPendingNominationQuery($campaign_id,$group_id,$from, $to);
        $status_nominations['pending_nominations'] = count($pending_nominationQuery->get());

        $overall_nominations = count(UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->get());
        $over_all_points_nominations = $this->common_repository->getCommonNominationQuery(0,$group_id,$from, $to);
        $overall_nominationPoints = $over_all_points_nominations->selectRaw('SUM(points) AS total_count')->first();

        $campaign_total_points = array();
        if($campaign_id == '0'){

            $campaign_list = ValueSet::select('id')->where('status','1')->get();

            $status_nominations['total_nominations_count'] = count(UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->get());

            $declined_nominations = UserNomination::where(function($q){
                            $q->where('level_1_approval', '-1')
                            ->orWhere('level_2_approval', '-1');
                        })->whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->get();
            $status_nominations['declined_nominations'] = count($declined_nominations);

            foreach($campaign_list as $key=>$campaign){
                $campaign_name = $this->common_repository->getCampaignName($campaign->id);

                $getnomination_query = $this->common_repository->getCommonNominationQuery($campaign->id,$group_id,$from, $to);

                if($pdf_filter == 'month'){
                    $nomination_point_data = $getnomination_query->select( DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))->selectRaw('SUM(points) AS entries')->groupBy('monthyear')->get();
                    $nomination_data = $getnomination_query->select(DB::raw("COUNT( 'created_at' ) AS entries"),  DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))->distinct()->groupBy('monthyear')->get();
                    $pdf_filter_name = 'monthyear';
                }else{
                    $nomination_point_data = $getnomination_query->select(DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.''))->selectRaw('SUM(points) AS entries')->groupBy($pdf_filter)->get();
                    $nomination_data = $getnomination_query->select(DB::raw("COUNT( 'created_at' ) AS entries"), DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.''))->distinct()->groupBy($pdf_filter)->get();
                    $pdf_filter_name = $pdf_filter;
                }

                if(count($nomination_data) > 0){
                    $campaing_nominations = array();
                    foreach($nomination_data as $k=>$data){
                        $campaing_nominations[$k]['entries']=$data->entries;
                        $campaing_nominations[$k][$pdf_filter_name]=$data->$pdf_filter_name;
                    }
                    $nomination_approved_data[$campaign_name] = $campaing_nominations;
                }

                if(count($nomination_point_data) > 0){
                    $campaing_nominations_points = array();
                    foreach($nomination_point_data as $ky=>$pointdata){
                        if($pointdata->entries != Null){
                            $Nomipoints = $pointdata->entries;
                        }else{
                            $Nomipoints = 0;
                        }
                        $campaing_nominations_points[$ky]['entries']=(int)$Nomipoints;
                        $campaing_nominations_points[$ky][$pdf_filter_name]=$pointdata->$pdf_filter_name;
                    }
                    $nomination_approved_points_data[$campaign_name] = $campaing_nominations_points;
                }


                $campaign_data = $this->common_repository->getCommonNominationQuery($campaign->id,$group_id,$from, $to);
                $campaign_points_count = $campaign_data->selectRaw('SUM(points) AS total_count')->first();
                $campaign_total_points[$campaign_name] = $campaign_points_count->total_count;


            }

        }else{
            $status_nominations['total_nominations_count'] = count(UserNomination::where('campaign_id',$campaign_id)->whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->get());

            $declined_nominations = UserNomination::where(function($q){
                            $q->where('level_1_approval', '-1')
                            ->orWhere('level_2_approval', '-1');
                        })->where('campaign_id',$campaign_id)->whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->get();

            $status_nominations['declined_nominations'] = count($declined_nominations);

            $campaign_name = $this->common_repository->getCampaignName($campaign_id);
            $getnomination_query = $this->common_repository->getCommonNominationQuery($campaign_id,$group_id,$from, $to);

            if($pdf_filter == 'month'){
                $nomination_point_data = $getnomination_query->select( DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))->selectRaw('SUM(points) AS entries')->distinct()->groupBy('monthyear')->get();
                $nomination_data = $getnomination_query->select(DB::raw("COUNT( 'created_at' ) AS entries"), DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))->distinct()->groupBy('monthyear')->get();
                $pdf_filter_name = 'monthyear';
            }else{
                $nomination_point_data = $getnomination_query->select(DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.''))->selectRaw('SUM(points) AS entries')->distinct()->groupBy($pdf_filter)->get();
                $nomination_data = $getnomination_query->select(DB::raw("COUNT( 'created_at' ) AS entries"), DB::raw(' '.$pdf_filter.'(created_at) as '.$pdf_filter.''))->distinct()->groupBy($pdf_filter)->get();
                $pdf_filter_name = $pdf_filter;
            }

            if(count($nomination_data) > 0){
                $campaing_nominations = array();
                foreach($nomination_data as $k=>$data){
                    $campaing_nominations[$k]['entries']=$data->entries;
                    $campaing_nominations[$k][$pdf_filter_name]=$data->$pdf_filter_name;
                }
                $nomination_approved_data[$campaign_name] = $campaing_nominations;
            }

            if(count($nomination_point_data) > 0){
                $campaing_nominations_points = array();
                foreach($nomination_point_data as $ky=>$pointdata){
                    if($pointdata->entries != Null){
                        $Nomipoints = $pointdata->entries;
                    }else{
                        $Nomipoints = 0;
                    }
                    $campaing_nominations_points[$ky]['entries']=(int)$Nomipoints;
                    $campaing_nominations_points[$ky][$pdf_filter_name]=$pointdata->$pdf_filter_name;
                }
                $nomination_approved_points_data[$campaign_name] = $campaing_nominations_points;
            }

            $campaign_data = $this->common_repository->getCommonNominationQuery($campaign_id,$group_id,$from, $to);
            $campaign_points_count = $campaign_data->selectRaw('SUM(points) AS total_count')->first();
            $campaign_total_points[$campaign_name] = $campaign_points_count->total_count;

        }

        #approved_nomination_pointd_graph
        $nomination_approved_points_graph = array();
        if(!empty($nomination_approved_points_data)){
            foreach($nomination_approved_points_data as $key=>$approved_nominationPoints){
                $inside_approved_points_data = array();
                foreach($approved_nominationPoints as $k=>$nomi_pointdata){
                    if(isset($nomi_pointdata['date'])){
                        $inside_approved_points_data['nomination_point_label'][] = date('d M Y', strtotime($nomi_pointdata['date']));
                    }else if(isset($nomi_pointdata['week'])){
                        $inside_approved_points_data['nomination_point_label'][] = $this->common_repository->getStartAndEndDate($nomi_pointdata['week'],$year_for_week);
                    }else{
                        $inside_approved_points_data['nomination_point_label'][] = Carbon::createFromFormat('m-Y', $nomi_pointdata['monthyear'])->format('M Y');
                    }

                    $inside_approved_points_data['nomination_point_count'][] = $nomi_pointdata['entries'];

                }
                $nomination_approved_points_graph[$key] = $inside_approved_points_data;
            }
        }

        $Get_final_approved_points_data = $this->common_repository->getFinalAllDatesNominations($nomination_approved_points_graph,$data_pdf_filter,$from,$to,$year_for_week);

        #Approved_nomination_graph
        $nomination_approved_graphData = array();
        if(!empty($nomination_approved_data)){
            foreach($nomination_approved_data as $key=>$approved_nominationData){
                $inside_approved_data = array();
                foreach($approved_nominationData as $k=>$nomi_data){
                    if(isset($nomi_data['date'])){
                        $inside_approved_data['nomination_point_label'][] = date('d M Y', strtotime($nomi_data['date']));
                    }else if(isset($nomi_data['week'])){
                        $inside_approved_data['nomination_point_label'][] = $this->common_repository->getStartAndEndDate($nomi_data['week'],$year_for_week);
                    }else{
                        $inside_approved_data['nomination_point_label'][] = Carbon::createFromFormat('m-Y', $nomi_data['monthyear'])->format('M Y');
                    }

                    $inside_approved_data['nomination_point_count'][] = $nomi_data['entries'];

                }
                $nomination_approved_graphData[$key] = $inside_approved_data;
            }
        }

        $Get_final_approved_data = $this->common_repository->getFinalAllDatesNominations($nomination_approved_graphData,$data_pdf_filter,$from,$to,$year_for_week);

        $status_nominations['approved_nominations'] = $awards_issued;

        $status_percentage_nominations['total_nominations'] = $status_nominations['total_nominations_count'];
        if($status_percentage_nominations['total_nominations'] == 0){
            $status_percentage_nominations['approved_nomination'] = 0;
            $status_percentage_nominations['pending_nominations'] = 0;
            $status_percentage_nominations['declined_nominations'] = 0;
        }else{
            $status_percentage_nominations['approved_nomination'] = round(($status_nominations['approved_nominations']/$status_nominations['total_nominations_count'] *100),2);
            $status_percentage_nominations['pending_nominations'] = round(($status_nominations['pending_nominations']/$status_nominations['total_nominations_count'] *100),2);
            $status_percentage_nominations['declined_nominations'] = round(($status_nominations['declined_nominations']/$status_nominations['total_nominations_count'] *100),2);

        }

        $status_percentage_nominations['label'] = 'Status of nominations';
        $status_percentage_nominations['label_value'] = $status_nominations['total_nominations_count']." nominations";

        #get_colors
        $campaign_colors = $this->common_repository->getCampaignColors();

        #Cost_of_award_as_per_campaign
        //$overall_nominations
        //$overall_nominationPoints
        $campaing_point_percentage = array();
        if(!empty($campaign_total_points)){
            $count = 0;
            foreach($campaign_total_points as $camp_name=>$camp_value){
                if($overall_nominationPoints->total_count == 0){
                    $campaing_point_percentage[$count]['name'] = $camp_name;
                    $campaing_point_percentage[$count]['points_percentage'] = 0;
                    $campaing_point_percentage[$count]['color'] = $campaign_colors[$camp_name];
                    //$campaing_point_percentage[$count]['color'] = '#'.substr(md5(rand()), 0, 6);
                }else{
                    $campaing_point_percentage[$count]['name'] = $camp_name;
                    $campaing_point_percentage[$count]['points_percentage'] = round($camp_value/$overall_nominationPoints->total_count * 100,2);
                    $campaing_point_percentage[$count]['color'] = $campaign_colors[$camp_name];
                    //$campaing_point_percentage[$count]['color'] = '#'.substr(md5(rand()), 0, 6);
                }
                $count++;
            }#end_foreach
        }#end_if

        $campaing_point_percentage['label'] = 'Cost of awards';
        $campaing_point_percentage['label_value'] = $currency_code." ".round((float)$coins_awarded->total_count / (float)$currency_points,2);

        $final_array['awards_issued'] = $awards_issued;
        $final_array['coins_awarded'] = (int)$coins_awarded->total_count;
        $final_array['budget_available'] = (int)$pending_data->total_count;
        $final_array['cost_rewards'] = $currency_code." ".round((float)$coins_awarded->total_count / (float)$currency_points,2);
        $final_array['nomination_approved_points_graphData'] = $Get_final_approved_points_data;
        $final_array['nomination_approved_graphData'] = $Get_final_approved_data;
        $final_array['status_of_nomination'] = $status_percentage_nominations;
        $final_array['cost_of_awards_percentage'] = $campaing_point_percentage;

        return $final_array;

    }/********recognition_report_ends_here********/


    /***************************
    fn for the products report
    **************************/
    public function filterRewardsReport($data,$group_id,$user_country){

        $final_array = array();
        $data_pdf_filter = $data['pdf_filter'];

        if(isset($data['pdf_filter']) && $data['pdf_filter'] != ''){
            $pdf_filter = $data['pdf_filter'];
        }else{
            $pdf_filter = '0';
        }
        if($pdf_filter == '1'){
            $pdf_filter = "week";
        }else if($pdf_filter == '2'){
            $pdf_filter = "month";
        }else{
            $pdf_filter = "date";
        }

        $from = $data['from'] . ' 00:00:01';
        $to = $data['to'] . ' 23:59:59';
        $year_for_week = date('Y', strtotime($from));

        $account_ids = UsersGroupList::select('account_id')->whereIn('user_group_id',$group_id)->distinct()->get();

        $account_id = array();
        if(!empty($account_ids)){
            foreach($account_ids as $key=>$account){
                $account_id[] = $account['account_id'];
            }
        }

        $currency_id = '1';
        $currency_code = 'AED';
        $currency_points = $this->common_repository->getAccountCurrencyPoints($currency_id);

        $order_placed = $this->common_repository->getOrderPlacedQuery($account_id,$from,$to,$user_country);
        $total_order_placed = count($order_placed->get());
        $total_points_orders = $order_placed->selectRaw('SUM(product_orders.value) AS total_count')->first();

        $order_shipped = $this->common_repository->getOrderShippedQuery($account_id,$from,$to,$user_country);
        $total_points_shipped = $order_shipped->selectRaw('SUM(product_orders.value) AS total_count')->first();

        if($pdf_filter == 'month'){
            $order_shipped_points_filter = ProductOrder::select(DB::raw("CONCAT_WS('-',MONTH(product_orders.created_at),YEAR(product_orders.created_at)) as monthyear"))->selectRaw('SUM(product_orders.value) AS entries')->join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereIn('product_orders.account_id',$account_id)->where('product_orders.status','3')->whereBetween('product_orders.created_at', [$from, $to])->groupBy('monthyear')->get();

            $total_order_placed_filter = ProductOrder::select(DB::raw("COUNT( 'product_orders.created_at' ) AS entries"),DB::raw("CONCAT_WS('-',MONTH(product_orders.created_at),YEAR(product_orders.created_at)) as monthyear"))->join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to])->groupBy('monthyear')->get();
        }else{
            $order_shipped_points_filter = ProductOrder::select(DB::raw(' '.$pdf_filter.'(product_orders.created_at) as '.$pdf_filter.''))->selectRaw('SUM(product_orders.value) AS entries')->join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereIn('product_orders.account_id',$account_id)->where('product_orders.status','3')->whereBetween('product_orders.created_at', [$from, $to])->groupBy($pdf_filter)->get();

            $total_order_placed_filter = ProductOrder::select(DB::raw("COUNT( 'product_orders.created_at' ) AS entries"),DB::raw(' '.$pdf_filter.'(product_orders.created_at) as '.$pdf_filter.''))->join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to])->groupBy($pdf_filter)->get();
        }

        $year_for_week = date('Y', strtotime($from));

        $order_data_points_graph = array();
        if(!$order_shipped_points_filter->isEmpty()){
            foreach($order_shipped_points_filter as $key=>$order_data){
                if(isset($order_data['date'])){
                    $order_data_points_graph['data_label'][] = date('d M Y', strtotime($order_data['date']));
                }else if(isset($order_data['week'])){
                    $order_data_points_graph['data_label'][] = $this->common_repository->getStartAndEndDate($order_data['week'],$year_for_week);
                }else{
                    $order_data_points_graph['data_label'][] = Carbon::createFromFormat('m-Y', $order_data['monthyear'])->format('M Y');
                }

                $order_data_points_graph['data_count'][] = $order_data['entries'];
                $order_data_points_graph['label'] = 'Value of redemptions';
                $order_data_points_graph['label_value'] = $total_points_shipped->total_count." Points";
            }
        }

        if( (isset($order_data_points_graph['data_label']) && isset($order_data_points_graph['data_count']))){
            $order_shipped_points_graph = $this->common_repository->getFinalGraphData($order_data_points_graph,$data_pdf_filter,$from,$to,$year_for_week);
        }else{
            $order_shipped_points_graph = $order_data_points_graph;
        }


        $order_placed_graph = array();
        if(!$total_order_placed_filter->isEmpty()){
            foreach($total_order_placed_filter as $key=>$orderdata){
                if(isset($orderdata['date'])){
                    $order_placed_graph['data_label'][] = date('d M Y', strtotime($orderdata['date']));
                }else if(isset($orderdata['week'])){
                    $order_placed_graph['data_label'][] = $this->common_repository->getStartAndEndDate($orderdata['week'],$year_for_week);
                }else{
                    $order_placed_graph['data_label'][] = Carbon::createFromFormat('m-Y', $orderdata['monthyear'])->format('M Y');
                }

                $order_placed_graph['data_count'][] = $orderdata['entries'];
                $order_placed_graph['label'] = 'Total redemptions';
                $order_placed_graph['label_value'] = $total_order_placed." redemptions";
            }
        }

        if( (isset($order_placed_graph['data_label']) && isset($order_placed_graph['data_count']))){
            $order_placed_count_graph = $this->common_repository->getFinalGraphData($order_placed_graph,$data_pdf_filter,$from,$to,$year_for_week);
        }else{
            $order_placed_count_graph = $order_placed_graph;
        }


        #points_redeemed/not redeemed percenatge
        $point_not_redeemed = $total_points_orders->total_count - $total_points_shipped->total_count;
        $points_status['label'] = 'Points awarded';
        $points_status['label_value'] = $total_points_orders->total_count;
        if($total_points_orders->total_count == 0){
            $points_status['points_redeemed_percentage'] = 0;
            $points_status['points_not_redeemed_percentage'] = 0;
        }else{
            $points_status['points_redeemed_percentage'] = ($total_points_shipped->total_count != "")?round($total_points_shipped->total_count/$total_points_orders->total_count * 100 ,2):0;
            $point_not_redeemed = $total_points_orders->total_count - $total_points_shipped->total_count;
            $points_status['points_not_redeemed_percentage'] = ($point_not_redeemed!='')?round($point_not_redeemed/$total_points_orders->total_count * 100 ,2):0;
        }


        #status_of_redemptions
        $cancel_orders = ProductOrder::join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->where('product_orders.status','-1')->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to])->selectRaw('COUNT(product_orders.created_at) AS total_count')->first();

        $confirmed_order = ProductOrder::join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->where('status','2')->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to])->selectRaw('COUNT(product_orders.created_at) AS total_count')->first();

        $pending_order = ProductOrder::join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->where('status','1')->whereIn('product_orders.account_id',$account_id)->whereBetween('product_orders.created_at', [$from, $to])->selectRaw('COUNT(product_orders.created_at) AS total_count')->first();

        $shipped_order = $this->common_repository->getOrderShippedQuery($account_id,$from,$to,$user_country);
        $shipped_order = $shipped_order->selectRaw('COUNT(product_orders.created_at) AS total_count')->first();

        if($total_order_placed == 0){
            $redemption_status['cancelled_orders'] = 0;
            $redemption_status['shipped_orders'] = 0;
            $redemption_status['confirmed_order'] = 0;
            $redemption_status['pending_orders'] = 0;
        }else{
            $redemption_status['cancelled_orders'] = ($cancel_orders->total_count)?round($cancel_orders->total_count/$total_order_placed * 100 ,2):0;
            $redemption_status['shipped_orders'] = ($shipped_order->total_count)?round($shipped_order->total_count/$total_order_placed * 100 ,2):0;
            $redemption_status['confirmed_order'] = ($confirmed_order->total_count)?round($confirmed_order->total_count/$total_order_placed * 100 ,2):0;
            $redemption_status['pending_orders'] = ($pending_order->total_count)?round($pending_order->total_count/$total_order_placed * 100 ,2):0;
        }
        $redemption_status['label'] = 'Status of redemptions';

        #rewards_available_percentage_data
        $total_products = Product::join('products_countries','products_countries.product_id','products.id')->where('products_countries.country_id',$user_country)->get();
        $total_rewards = count($total_products);

        $product_id = array();
        foreach($total_products as $val){
            $product_id[] = $val->id;
        }

        $total_productviewed = ProductsAccountsSeen::whereIn('product_id',$product_id)->whereBetween('created_at', [$from, $to])->get();
        $total_viewed = count($total_productviewed);

        $order_placed_overall = ProductOrder::join('products_countries','products_countries.product_id','product_orders.product_id')->where('products_countries.country_id',$user_country)->whereBetween('product_orders.created_at', [$from, $to])->get();
        $overall_order_placed = count($order_placed_overall);

        $rewards_available_status['total_rewards'] = $total_rewards;
        if($total_rewards == 0){
            $rewards_available_status['ordered_percentage'] = 0;
            $rewards_available_status['viewed_percentage'] = 0;
            $rewards_available_status['never_viewed_percentage'] = 0;
        }else{
            $rewards_available_status['ordered_percentage'] = round($overall_order_placed/$total_rewards * 100 ,2);
            $rewards_available_status['viewed_percentage'] = round(($total_viewed-$overall_order_placed)/$total_rewards * 100 ,2);
            $rewards_available_status['never_viewed_percentage'] = round(($total_rewards-$total_viewed)/$total_rewards * 100 ,2);
        }
        $rewards_available_status['label'] = 'Rewards available';
        $rewards_available_status['label_value'] = $total_rewards." rewards";

        $final_array['reward_redemption'] = $total_order_placed;
        $final_array['points_awarded'] = $total_points_orders->total_count;
        $final_array['points_redeemed'] = $total_points_shipped->total_count;
        $final_array['cost_of_rewards'] = $currency_code." ".($total_points_shipped->total_count!="")?round((float)$total_points_shipped->total_count / (float)$currency_points,2):0;
        $final_array['value_redemption_graph'] = $order_shipped_points_graph;
        $final_array['redemption_graph'] = $order_placed_count_graph;
        $final_array['points_awarded_percentage'] = $points_status;
        $final_array['redemption_percentage'] = $redemption_status;
        $final_array['rewards_available'] = $rewards_available_status;
        $final_array['most_popular_rewards'] = $this->common_repository->getMostPopularRewards($from,$to,$user_country);

        return $final_array;
    }/********rewards_report_ends_here**********/


}
