<?php namespace Modules\Nomination\Exports;

use Modules\Program\Models\UsersEcards;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use Carbon\Carbon;

/**
 * Class UsersExport
 * @package Modules\User\Exports
 */
class SendEcardsCampaignRecordsExports implements WithHeadings, WithMapping ,FromCollection
{
	protected $param;

    public function __construct($param = '')
    {
       $this->param = $param;
    }
	
	public function map($records) :array
    {
		$status = "-"; 
		if(($records->level_1_approval == 1 && $records->level_2_approval == 1) || ($records->level_1_approval == 1 && $records->level_2_approval == 2) || ($records->level_1_approval == 2 && $records->level_2_approval == 1))
		{
			$status = "Approved";
		}
		else if(($records->level_1_approval == 0 && $records->level_2_approval == 0) || ($records->level_1_approval == 2 && $records->level_2_approval == 0) || ($records->level_1_approval == 0 && $records->level_2_approval == 2)) {
			$status = "Pending";
		}
		else if($records->level_1_approval == 1 && $records->level_2_approval == 0)
		{
			$status = "L1 Approved";
		}
		else if($records->level_1_approval == -1)
		{
			$status = "L1 Rejected";
		}
		else if(($records->level_1_approval == 1 || $records->level_1_approval == 2) && $records->level_2_approval == -1)
		{
			$status = "L2 Rejected";
		}
		// else if($records->level_1_approval == -1 && $records->level_2_approval == -1)
		// {
		// 	$status = "Rejected";
		// }
		try{
            return [
                'Recipient Name' => ucfirst($records->nominated_user->first_name) ." " .ucfirst($records->nominated_user->last_name),
                'Recipient Email' => $records->nominated_user->email,
				'Sender Name' => ucfirst($records->nominated_by->first_name) ." " .ucfirst($records->nominated_by->last_name),
                'Sender Email' => $records->nominated_by->email,
                'Points' => (isset($records->points) && !empty($records->points)) ? $records->points : "-",
                'Status' => $status,
                'Date' => (new Carbon($records->created_at))->format('M d, Y H:i a'),
            ];
        }catch (\Exception $exception){
            throw $exception;
        }
		
    }
	
    public function headings(): array
    {
        return ['Recipient Name', 'Recipient Email', 'Sender Name', 'Sender Email', 'Points', 'Status', 'Date'];
    }

    public function collection()
    {
		$campaignID = (!empty($this->param) && isset($this->param['campaignID']) && !empty($this->param['campaignID'])) ? $this->param['campaignID'] : false;
		$sender_id 	= (!empty($this->param) && isset($this->param['sender_id']) && !empty($this->param['sender_id'])) ? $this->param['sender_id'] : false;
		
		if(!empty($campaignID))
		{
			$data = UsersEcards::where('users_ecards.campaign_id',$campaignID)
								->leftJoin('user_nominations', 'user_nominations.ecard_id', '=', 'users_ecards.id');			
					
			if(isset($sender_id) && !empty($sender_id)) {
				$data->where('users_ecards.sent_by', $sender_id);
			}
					
			$data->orderBy('users_ecards.id','desc')
					->with(['nominated_user','nominated_by']);
				
			$result = $data->get();
			
			return $result;
		}
		
    }
	
}
