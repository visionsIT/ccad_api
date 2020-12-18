<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Nomination\Models\ValueSet;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersGroupList;
use Modules\Account\Models\Account;
use Modules\User\Models\UserCampaignsBudget;

class RippleSettingsRepository extends Repository
{
    protected $modeler = CampaignSettings::class;

    /**
     * @param Get Ripple Setting Data
     * @return mixed
     */
    public function getRippleSettingsBy($id)
    {
        return CampaignSettings::where('campaign_id', $id)->get();
    }

    public function getCampaignIDBySLug($slug)
    {
        return ValueSet::where('campaign_slug', $slug)->first();
    }
    public function getCampaignNameById($nameval,$campaignId)
    {
        return ValueSet::select('name')->where('name', $nameval)->where('id', '!=', $campaignId)->get();
    }
    public function getDataCampaignID($campaignId)
    {
        return CampaignSettings::where('campaign_id', $campaignId)->first();
    }

    public function getRippleBudget($requestdata)
    {


        $getCampaignData = $this->getCampaignIDBySLug($requestdata->camp_slug);
        $campaign_id = $getCampaignData->id;

        return UserCampaignsBudget::select('budget as ripple_budget')

            ->where('program_user_id', $requestdata->program_user_id)
            ->where('campaign_id', $campaign_id)
            ->first();    

        //return ProgramUsers::select('ripple_budget')->where('email', $email_address)->first();
    }
   
    public function getRippleBudgetBYProgramId($programId)
    {
        return ProgramUsers::select('ripple_budget')->where('id', $programId)->first();
    }
   

    public function getLevel1Leads($receiverid)
    {

        return $receiverGroupId = UsersGroupList::join('program_users as t1', "t1.account_id","=","users_group_list.account_id")
            ->where('t1.id',$receiverid)
            ->where('users_group_list.status','1')
            ->select("users_group_list.user_group_id","users_group_list.user_role_id")
            ->orderBy('users_group_list.user_role_id','ASC') // Get Lowest role Group Ic
            ->first()->toArray();
       /* return UsersGroupList::select('user_group_id','user_role_id')
            ->where('user_group_id', $receiverGroupId['user_group_id'])
            ->where('user_role_id', '!=', $receiverGroupId['user_role_id'])
            ->get()->toArray();*/

        
    }
    
}
