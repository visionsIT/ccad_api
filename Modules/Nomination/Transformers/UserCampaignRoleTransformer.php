<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Helper;

class UserCampaignRoleTransformer extends TransformerAbstract
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
            'account_id' => Helper::customCrypt($data->account_id),
            'name' => ucfirst($data->first_name).' '.ucfirst($data->last_name),
            'email' => $data->email
        ];
    }
}
