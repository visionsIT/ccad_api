<?php namespace Modules\Agency\Repositories;

use App\Repositories\Repository;
use Modules\Agency\Models\Client;

class ClientRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = Client::class;


    /**
     * @param Client $client
     * @param $ids
     */
    public function attachCataloguesToClient(Client $client, $ids): void
    {
        $client->catalogues()->attach($ids);
    }

    /**
     * @param Client $client
     * @param $ids
     */
    public function syncCataloguesToClient(Client $client, $ids): void
    {
        $client->catalogues()->sync($ids);
    }

    /**
     * @param Client $client
     * @param $ids
     */
    public function detachCataloguesToClient(Client $client, $ids): void
    {
        $client->catalogues()->detach($ids);
    }

}
