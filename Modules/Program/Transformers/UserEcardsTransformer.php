<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Program;
use Modules\Program\Models\UsersEcards;
use Helper;
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

        $nominatedto = $UsersEcards->nominatedto->toArray();
        $toId = Helper::customCrypt($nominatedto['id']);
        $toAccountId = Helper::customCrypt($nominatedto['account_id']);
        $toCountryId = Helper::customCrypt($nominatedto['country_id']);
        unset($nominatedto['id']);
        unset($nominatedto['account_id']);
        unset($nominatedto['country_id']);
        $nominatedto['id'] = $toId;
        $nominatedto['account_id'] = $toAccountId;
        $nominatedto['country_id'] = $toCountryId;


        $nominatedby = $UsersEcards->nominatedby->toArray();
        $toId = Helper::customCrypt($nominatedby['id']);
        $toAccountId = Helper::customCrypt($nominatedby['account_id']);
        $toCountryId = Helper::customCrypt($nominatedby['country_id']);
        unset($nominatedby['id']);
        unset($nominatedby['account_id']);
        unset($nominatedby['country_id']);
        $nominatedby['id'] = $toId;
        $nominatedby['account_id'] = $toAccountId;
        $nominatedby['country_id'] = $toCountryId;
        
        return [
            'id'                        => Helper::customCrypt($UsersEcards->cardid),
            'campaign_id'               => Helper::customCrypt($UsersEcards->campaign_id),
            'ecard_id'                  => Helper::customCrypt($UsersEcards->ecard_id),
            'image_message'             => $UsersEcards->image_message,
            'attachment'               => ($UsersEcards->new_image !='')?$imgUrl.$UsersEcards->new_image:'',
            'sent_to'                   => Helper::customCrypt($UsersEcards->sent_to),
            'nominated_user'            => $nominatedto,
            'sent_by'                   => Helper::customCrypt($UsersEcards->sent_by),
            'nominated_by'              => $nominatedby,
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
            'ecard_date'                => date('d/m/Y', strtotime($UsersEcards->card_create)),

        ];
    }
}
