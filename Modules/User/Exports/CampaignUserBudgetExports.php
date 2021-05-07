<?php namespace Modules\User\Exports;

use Modules\User\Models\UserCampaignsBudget;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use Carbon\Carbon;

/**
 * Class UsersExport
 * @package Modules\User\Exports
 */
class CampaignUserBudgetExports implements WithHeadings, WithMapping ,FromCollection
{
	protected $param;

    public function __construct($param = '')
    {
       $this->param = $param;
    }

	public function map($records) :array
    {		
		try{
			$first_name = (isset($records->user->first_name)) ? ucfirst($records->user->first_name) : '';
			$last_name 	= (isset($records->user->last_name)) ? ucfirst($records->user->last_name) : '';
            return [
                'Employee' => $first_name ." " .$last_name,
                'Mail' => (isset($records->user->email)) ? $records->user->email : false,
                'Available Point' => (isset($records->budget)) ? $records->budget: 0,
            ];
        }catch (\Exception $exception){
            throw $exception;
        } 	
    }

    public function headings(): array
    {
        return ['Employee', 'Mail', 'Available Point'];
    }

    public function collection()
    {
		$campaignID = (!empty($this->param) && isset($this->param['campaignID']) && !empty($this->param['campaignID'])) ? $this->param['campaignID'] : false;

		if(!empty($campaignID))
		{
			$result = UserCampaignsBudget::select('user_campaigns_budget.*')
							->with(['user'])
							->leftJoin('program_users', 'program_users.id', '=', 'user_campaigns_budget.program_user_id')
							->where('user_campaigns_budget.campaign_id','=',$campaignID)
							->orderBy('program_users.first_name','ASC')
							->get();
				
			return $result;
			
		}
		
    }

}
