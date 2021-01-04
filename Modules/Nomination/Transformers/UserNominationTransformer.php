<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\UserNomination;
use Modules\Account\Models\Account;

class UserNominationTransformer extends TransformerAbstract
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



        return [
            'id'                        => $model->id,
            'campaign_id'               => $model->campaignid,
            //'nomination_id'             => $model->campaign,
            'user'                      => $model->user,
            'nominated_user'            => $model->user_relation,
            'nominated_user_group_name' => $model->account->getRoleNames(),
            'account_id'                => $model->account_id,
            'nominated_by'              => $model->user_account,//$model->account,
            'nominated_by_group_name'   => $model->account->getRoleNames(),
            'user name'                 => optional($model->account)->user->first_name, //todo remove all optional and check all relation IN validation before insert
            'user email'                => optional($model->account)->email,
            'value'                     => ($model->points/10),
            'Type'                      => optional($model->type)->name ?? $model->reason,
            'value set'                 => optional($model->type)->value_set,
            'value_set_name'            => optional($model->type)->valueset,
            'level'                     => optional($model->level)->name,
            'points'                    => $model->points,
            'logo'                      => optional($model->type)->logo,
            'reason'                    => $model->reason,
            'personal_message'          => $model->personal_message,
            'nominee_function'          => $model->nominee_function,
            'attachments'               => ($model->attachments !='')?$imgUrl.$model->attachments:'',
            'Approved for level 1'      => $model->level_1_approval,
            'Approved for level 2'      => $model->level_2_approval,
            'Approved for level 1 Id'   => $model->approver_account_id,
            'Approved for level 2 Id'   => $model->l2_approver_account_id,
            //'points'      => $model->points,
            'Decline reason'            => $model->decline_reason,
            'created_at'                => $model->created_at,
            'updated_at'                => $model->updated_at,
            'project_name'              => $model->project_name,
            'created_date_time'         => date('M d, Y h:i A', strtotime($model->created_at)), //April 15 2014 10:30pm

        ];
    }

}

