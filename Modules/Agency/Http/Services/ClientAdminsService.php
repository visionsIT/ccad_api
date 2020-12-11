<?php namespace Modules\Agency\Http\Services;

use Carbon\Carbon;
use DB;
use Modules\Account\Http\Services\AccountService;
use Modules\Agency\Http\Repositories\ClientAdminsRepository;

class ClientAdminsService
{
    protected $repository;

    /**
     * AgencyAdminsService constructor.
     *
     * @param ClientAdminsRepository $repository
     */
    public function __construct(ClientAdminsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $client
     *
     * @return mixed
     */
    public function get($client)
    {
        return $client->admins;
    }

    /**
     * @param $data
     * @param $client
     * @param AccountService $account_service
     * @param Carbon $carbon
     *
     * @return mixed
     * @throws \Exception
     */
    public function store($data, $client, AccountService $account_service, Carbon $carbon)
    {
        DB::beginTransaction();

        try {

            $account = $account_service->store($data + [ 'password' => str_random(), 'last_login' => $carbon->now() ]);

            $admin = $this->repository->create($data + [ 'client_id' => $client->id, 'account_id' => $account->id ]);

            DB::commit();

            return $admin;

        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $data
     * @param $id
     *
     * @throws \Exception
     */
    public function update( $data, $id): void
    {
       $this->repository->update($data, $id);
    }

    /**
     * @param $id
     */
    public function destroy($id): void
    {
        $this->repository->destroy($id);
    }
}
