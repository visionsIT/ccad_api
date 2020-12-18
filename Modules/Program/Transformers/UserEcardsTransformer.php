<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Program;
use Modules\Program\Models\UsersEcards;

class UserEcardsTransformer extends TransformerAbstract
{
    /**
     * @param Program $program
     *
     * @return array
     */
    public function transform(UsersEcards $UsersEcards): array
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/e_card_images/new/';
//echo "<pre>";print_r($UsersEcards->user_nominations);die;
        
        return [
            'id'                        => $UsersEcards->id,
            'campaign_id'               => $UsersEcards->campaign_id,
            'ecard_id'                  => $UsersEcards->ecard_id,
            'image_message'             => $UsersEcards->image_message,
            'attachment'               => ($UsersEcards->new_image !='')?$imgUrl.$UsersEcards->new_image:'',
            'sent_to'                   => $UsersEcards->sent_to,
            'nominated_user'            => $UsersEcards->nominatedto,
            'sent_by'                   => $UsersEcards->sent_by,
            'nominated_by'              => $UsersEcards->nominatedby,
            'points'                    => $UsersEcards->points,
            'send_type'                 => $UsersEcards->send_type,
            'user'                      => $UsersEcards->user,
            'name'                      => $UsersEcards->name,
            'description'               => $UsersEcards->description,
            'program_id'                => $UsersEcards->program_id,
            'status'                    => $UsersEcards->status,
            'campaign_type_id'          => $UsersEcards->campaign_type_id,
            'anchor_status'             => $UsersEcards->anchor_status,
            'campaign_slug'             => $UsersEcards->campaign_slug,
            'send_multiple_status'      => $UsersEcards->send_multiple_status,
            'wall_settings'             => $UsersEcards->wall_settings,
            'user_nominations'          => $UsersEcards->user_nominations,

        ];
    }
}
