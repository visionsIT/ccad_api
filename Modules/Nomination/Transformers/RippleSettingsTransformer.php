<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\CampaignSettings;
use DB;
class RippleSettingsTransformer extends TransformerAbstract
{
    /**
     * @param Campaign Settings
     *
     * @return array
     */
    public function transform(CampaignSettings $model): array
    {

        $campain_data =  DB::table('campaign_settings')->select('value_sets.name', 'value_sets.status')->where(['campaign_settings.id' => $model->id])->leftJoin('value_sets', 'value_sets.id', '=', 'campaign_settings.campaign_id')->get()->first();

        $ecards_data = DB::table('campaign_settings')->select('ecards.id','ecards.card_title', 'ecards.card_image', 'ecards.status', 'ecards.allow_points','ecards.campaign_id')->where(['campaign_settings.id' => $model->id])->rightJoin('ecards', 'ecards.campaign_id', '=', 'campaign_settings.campaign_id')->get();

        return [
            'id'  => $model->id,
            'campaign_id' => $model->campaign_id,
            'send_multiple_status'   => $model->send_multiple_status,
            'approval_request_status'  => $model->approval_request_status,
            'level_1_approval'  => $model->level_1_approval,
            'level_2_approval'  => $model->level_2_approval,
            'budget_type'  => $model->budget_type,
            'min_point' => $model->min_point,
            'max_point' => $model->max_point,
            's_eligible_user_option' =>  $model->s_eligible_user_option,
            's_level_option_selected' =>  $model->s_level_option_selected,
            's_user_ids' =>  $model->s_user_ids,
            's_group_ids' =>  $model->s_group_ids,
            'receiver_users' =>  $model->receiver_users,
            'receiver_group_ids' =>  $model->receiver_group_ids,
            'campaign_name'  => $campain_data->name,
            'campaign_status' => $campain_data->status,
            'e_card_data' => $ecards_data ? $ecards_data : NULL,
            'points_allowed' => $model->points_allowed,
        ];
    }

}
