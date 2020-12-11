<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Ecards;

class EcardsTransformer extends TransformerAbstract
{
    /**
     * @param Ecards $program
     *
     * @return array
     */
    public function transform(Ecards $eCard): array
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/e_card_images/';
        return [
            'id'            => $eCard->id,
            'name'          => $eCard->card_title,
            'status'        => $eCard->status,
            'card_image'    => ($eCard->card_image !='')?$eCard->card_image:'',
            'allow_points'  => $eCard->allow_points,
            'created_at'    => $eCard->created_at,
            'updated_at'    => $eCard->updated_at,
            'created_date_time' => date('M d, Y h:i A', strtotime($eCard->created_at)), //April 15 2014 10:30pm
        ];
    }
}
