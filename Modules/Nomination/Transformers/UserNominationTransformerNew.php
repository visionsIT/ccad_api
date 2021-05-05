<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\UserNomination;
use Modules\Account\Models\Account;
use Modules\Nomination\Models\CampaignLike;
use Modules\Nomination\Models\CampaignComment;
use DB;
use Helper;

class UserNominationTransformerNew extends TransformerAbstract
{
    /**
     * @param UserNomination $model
     *
     * @return array
     */
    public function transform(UserNomination $model): array
    {
		$user_id = (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? Helper::customDecrypt($_GET['user_id']) : false;
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/user_nomination_files/';
        
        //$wall_setting = $model->campaign->value_set_relation->wall_setings['wall_settings'];//for_wall_sett
      
		$comment_Array = array();
		$like_Array = array();
		$like_Nomination_Array = array();
		if(!empty($model->id)){
			$comment_where = array('user_nomination_id' => $model->id);
			$comment_Array = CampaignComment::where($comment_where)
							->join('program_users', 'campaign_comments.account_id', '=', 'program_users.account_id')
							->get(['campaign_comments.*','program_users.first_name','program_users.last_name','program_users.email','program_users.username','program_users.profile_image','program_users.image_path',DB::raw("DATE_FORMAT(campaign_comments.created_at, '%M %d, %Y %H:%i') as created_date")]);

			$like_where = array('user_nomination_id' => $model->id , 'is_like' => 1);
			$like_Array = CampaignLike::where($like_where)
						->join('program_users', 'campaign_likes.account_id', '=', 'program_users.account_id')
						->get(['campaign_likes.*','program_users.first_name','program_users.last_name','program_users.email','program_users.username','program_users.profile_image','program_users.image_path',DB::raw("DATE_FORMAT(campaign_likes.created_at, '%M %d, %Y %H:%i') as created_date")]);
						
			if(!empty($user_id))
			{
				$like_nomination_where = array('user_nomination_id' => $model->id ,'campaign_likes.account_id' => $user_id , 'is_like' => 1);
				$like_Nomination_Array = CampaignLike::where($like_nomination_where)
										->join('program_users', 'campaign_likes.account_id', '=', 'program_users.account_id')
										->get(['campaign_likes.*','program_users.first_name','program_users.last_name','program_users.email','program_users.username','program_users.profile_image','program_users.image_path',DB::raw("DATE_FORMAT(campaign_likes.created_at, '%M %d, %Y %H:%i') as created_date")]);
			}
        }
		
        return [
            'id'                        => $model->id,
            'campaign_id'               => $model->campaignid,
            //'campaign_type_id'          => $model->campaign->value_set_relation->campaign_id->id,
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
			'like_flag'             	=> $model->like_flag,
            'total_likes'           	=> count($like_Array),
            'likes'             		=> $like_Array,
            'user_likes'             	=> $like_Nomination_Array,
			'comment_flag'             	=> $model->comment_flag,
            'total_comments'            => count($comment_Array),
            'comments'             		=> $comment_Array,
           /* 'group_id' => $model->project_name,
            'campaign_id' => $model->project_name,
            'level_1_approval' => $model->project_name,
            'level_2_approval' => $model->project_name,
            'point_type' => $model->project_name,*/
        ];
   
    }

}

