<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\UserNomination;
use Modules\Account\Models\Account;

class TeamNominationTransformer extends TransformerAbstract
{
    /**
     * @param UserNomination $model
     *
     * @return array
     */
    public function transform(UserNomination $model): array
    {
        try {


        return [
            'id'                        =>  $model->id,
            'nomination_id'             =>  $model->campaign,
            'user'                      =>  $model->user,
            'nominated_user'            =>  [
                                    'id'        =>  $model->user_relation->account->id,
                                    'name'      =>  $model->user_relation->account->name,
                                    'email'     =>  $model->user_relation->account->email,
                                    'default_department_name'   =>  (!empty($model->user_relation->account->defaultDepartment)) ? $model->user_relation->account->defaultDepartment->name : '',
            ],
            'account_id'                =>  $model->account_id,
            'nominated_by'              =>  [
                                    'id'        =>  $model->account->id,
                                    'name'      =>  ucfirst($model->account->user->first_name).' '.ucfirst($model->account->user->last_name),//$model->account->name,
                                    'email'     =>  $model->account->email,
                                    'default_department_name'   =>  (!empty($model->account->defaultDepartment)) ? $model->account->defaultDepartment->name : '',
            ],
            'value'                     =>  ($model->points/10),//$model->value,
            'type'                      =>  optional($model->type)->name,
            'value set'                 =>  optional($model->type)->value_set,
            'value_set_name'            =>  optional($model->type)->valueset,
            'level'                     =>  optional($model->level)->name,
            'points'                    =>  $model->points,
            'logo'                      =>  optional($model->type)->logo,
            'reason'                    =>  $model->reason,
            'project_name'              =>  $model->project_name,
            'team_nomination'           =>  $model->team_nomination,
            'status'                    =>  $model->level_1_approval,
            'reject_reason'             =>  $model->reject_reason,
            'created_at'                      =>  $model->created_at
        ];

    }
    catch(exception  $ex){
        return [];
    }
    }

}

