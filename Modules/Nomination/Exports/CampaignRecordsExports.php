<?php namespace Modules\Nomination\Exports;

use Modules\Nomination\Models\UserNomination;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use Modules\Nomination\Http\Requests\UserNomination\GetRequest;
use Carbon\Carbon;

/**
 * Class UsersExport
 * @package Modules\User\Exports
 */
class CampaignRecordsExports implements WithHeadings, WithMapping ,FromCollection
{
	protected $param;

    public function __construct($param = '')
    {
       $this->param = $param;
    }
	
	public function map($records) :array
    {
	
		$status = "Pending"; 
		if($records->level_1_approval == 1 && $records->level_2_approval == 1)
		{
			$status = "Approved";
		}
		else if($records->level_1_approval == 1 && $records->level_2_approval == 0)
		{
			$status = "L1 Approved";
		}
		else if($records->level_1_approval == -1 && $records->level_2_approval == 0)
		{
			$status = "L1 Rejected";
		}
		else if($records->level_1_approval == 1 && $records->level_2_approval == -1)
		{
			$status = "L2 Rejected";
		}
		else if($records->level_1_approval == -1 && $records->level_2_approval == -1)
		{
			$status = "Rejected";
		}
		try{
            return [
                'Name' => ucfirst($records->nominated_user_first_name) ." " .ucfirst($records->nominated_user_last_name),
                'Eamil' => $records->nominated_user_email,
                'Country' => $records->nominated_user_country,
                'Status' => $status,
                'Date' => (new Carbon($records->created_at))->format('M d, Y H:i a'),
            ];
        }catch (\Exception $exception){
            throw $exception;
        }
		
    }
	
    public function headings(): array
    {
        return ['Name', 'Email', 'Country', 'Status', 'Date'];
    }

	public function collection()
    {
		$campaignID = (!empty($this->param) && isset($this->param['campaignID']) && !empty($this->param['campaignID'])) ? $this->param['campaignID'] : false;
		$search 	= (!empty($this->param) && isset($this->param['q']) && !empty($this->param['q'])) ? $this->param['q'] : false;
		$orderBy 	= (!empty($this->param) && isset($this->param['order']) && !empty($this->param['order'])) ? $this->param['order'] : false;
		$col 		= (!empty($this->param) && isset($this->param['col']) && !empty($this->param['col'])) ? $this->param['col'] : false;
		$from 		= (!empty($this->param) && isset($this->param['from']) && !empty($this->param['from'])) ? $this->param['from'] : false;
		$to 		= (!empty($this->param) && isset($this->param['to']) && !empty($this->param['to'])) ? $this->param['to']. ' 23:59:00' : false;
		
		if(!empty($campaignID))
		{
			$data = UserNomination::where('campaign_id',$campaignID)
					->join('program_users  as t1', 'user_nominations.user', '=', 't1.account_id')					
					->join('program_users  as t2', 'user_nominations.account_id', '=', 't2.account_id')
					->leftJoin('value_sets', 'user_nominations.value', '=', 'value_sets.id')
					->leftJoin('campaign_types', 'campaign_types.id', '=', 'value_sets.campaign_type_id');			
			
			if(isset($search) && !empty($search)) {
				$data->where(function($query1) use ($search){
					$query1->where('t1.first_name', 'LIKE', "%{$search}%")
					->orWhere('t1.email', 'LIKE', "%{$search}%")
					->orWhere('t2.first_name', 'LIKE', "%{$search}%")
					->orWhere('t2.email', 'LIKE', "%{$search}%");
				});
			}

			if(isset($from) && isset($to) && !empty($from) && !empty($to)) {
				$data->whereBetween('user_nominations.created_at', [$from, $to]);
			}
			
			if(isset($col) && !empty($col)) {
				if($col == 'nomination_name') {
					$data->orderBy('t2.first_name',$orderBy);
				} else if ($col == 'nominator_name') {
					$data->orderBy('t1.first_name',$orderBy);
				} else if($col == 'nomination_email') {
					$data->orderBy('t2.email',$orderBy);
				} else if($col == 'nominator_email') {
					$data->orderBy('t1.email',$orderBy);
				} else if($col == 'created_date_time') {
					$data->orderBy('user_nominations.created_at',$orderBy);
				} else {
					$data->orderBy('user_nominations.id',$orderBy);
				}
			}
			else
			{
				$data->orderBy('user_nominations.id','desc');
			}
			
			$result = $data->get([
				't1.first_name as nominated_user_first_name',
				't1.last_name as nominated_user_last_name',
				't1.email as nominated_user_email',
				't1.country as nominated_user_country',
				't2.first_name as nominated_by_user_first_name',
				't2.last_name as nominated_by_user_last_name',
				't2.email as nominated_by_user_email',
				't2.country as nominated_by_user_country',
				'campaign_type',
				'points',
				'level_1_approval',
				'level_2_approval',
				'user_nominations.created_at'
			]);
			
			//echo '<pre>'; print_r($result->toArray()); die;
			return $result;
		}
		
    }

}
