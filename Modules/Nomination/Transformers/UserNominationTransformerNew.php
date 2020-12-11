<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\UserNomination;
use Modules\Account\Models\Account;

class UserNominationTransformerNew extends TransformerAbstract
{
    /**
     * @param UserNomination $model
     *
     * @return array
     */
    public function transform(UserNomination $model): array
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/user_nomination_files/';
        
        //$wall_setting = $model->campaign->value_set_relation->wall_setings['wall_settings'];//for_wall_sett
       
        return [
            'id'                        => $model->id,
            'campaign_id'               => $model->campaignid,
            'campaign_type_id'          => $model->campaign->value_set_relation->campaign_id->id,
            //'nomination_id'             => $model->campaign,
            'user'                      => $model->user,
            'nominated_user'            => $model->user_relation,
            'nominated_user_group_name' => $model->account->getRoleNames(),
            'account_id'                => $model->account_id,
            'nominated_by'              => $model->user_account,//$model->account,
            'nominated_by_group_name'   => $model->account->getRoleNames(),
            'user name'                 => optional($model->account)->name, //todo remove all optional and check all relation IN validation before insert
            'user email'                => optional($model->account)->email,
            'value'                     => ($model->points/10),
            'Type'                      => optional($model->type)->name ?? $model->reason,
            'value set'                 => optional($model->type)->value_set,
            'value_set_name'            => optional($model->type)->valueset,
            'level'                     => optional($model->level)->name,
            'points'                    => $model->points,
            'logo'                      => optional($model->type)->logo,
            'reason'                    => $model->reason,
            'attachments'               => ($model->attachments !='')?$imgUrl.$model->attachments:'',
            'Approved for level 1'      => $model->level_1_approval,
            'Approved for level 2'      => $model->level_2_approval,
            //'points'      => $model->points,
            'Decline reason'            => $model->decline_reason,
            'created_at'                => $model->created_at,
            'updated_at'                => $model->updated_at,
            'project_name'              => $model->project_name,
            'created_date_time'         => date('M d, Y h:i A', strtotime($model->created_at)), //April 15 2014 10:30pm
           /* 'group_id' => $model->project_name,
            'campaign_id' => $model->project_name,
            'level_1_approval' => $model->project_name,
            'level_2_approval' => $model->project_name,
            'point_type' => $model->project_name,*/
        ];
   
    }

}

