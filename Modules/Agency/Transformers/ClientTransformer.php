<?php namespace Modules\Agency\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Agency\Models\Client;

class ClientTransformer extends TransformerAbstract
{
    /**
     * @param Client $client
     *
     * @return array
     * @throws \Laracodes\Presenter\Exceptions\PresenterException
     */
    public function transform(Client $client): array
    {
        return [
            'id'             => $client->id,
            'agency'         => $client->agency->name,
            'name'           => $client->name,
            'contact_name'   => $client->contact_name,
            'contact_email'  => $client->contact_email,
            'logo'           => $client->logo,
            'accent_color'   => $client->accent_color,
            'admins_count'   => $client->present()->admins_count,
            'programs_count' => $client->present()->programs_count,
            'catalogues'     => $client->catalogues()->pluck('name') // needs to edit
        ];
    }
}
