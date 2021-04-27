<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Helper;

class GetUserCampaignRoleTransformer extends TransformerAbstract
{
    /**
     * @param UserCampaignRole $User
     *
     * @return array
     */
    public function transform($data): array
    {		
        return [
            'id' => $data->id,
            'campaign_id' => $data->campaign_id,
            'account_id' => Helper::customCrypt($data->account_id),
            'name' => ucfirst($data->first_name).' '.ucfirst($data->last_name),
            'user_role_id' => $data->user_role_id,
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
            'role_name' => $data->role_name,
        ];
    }
}
