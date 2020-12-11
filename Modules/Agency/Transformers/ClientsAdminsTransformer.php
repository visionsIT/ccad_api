<?php namespace Modules\Agency\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Agency\Models\ClientsAdmin;

class ClientsAdminsTransformer extends TransformerAbstract
{
    /**
     * @param ClientsAdmin $model
     *
     * @return array
     */
    public function transform(ClientsAdmin $model): array
    {
        return [
            'id'             => $model->id,
            'name'           => ucfirst($model->account->user->first_name).' '.ucfirst($model->account->user->last_name),//$model->account->name,
            'email'          => $model->account->email,
            'contact_number' => $model->account->contact_number,
            'type'           => $model->account->type,
            'last_login'     => $model->account->last_login,
            'account_id'     => $model->account_id,
            'client_id'      => $model->client_id,
        ];
    }
}
