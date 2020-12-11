<?php namespace Modules\Agency\Http\Services;

use Carbon\Carbon;
use DB;
use Modules\Account\Http\Services\AccountService;
use Modules\Agency\Http\Repositories\AgencyAdminsRepository;

class AgencyAdminsService
{
    protected $repository;

    /**
     * AgencyAdminsService constructor.
     *
     * @param AgencyAdminsRepository $repository
     */
    public function __construct(AgencyAdminsRepository $repository)
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
     * @param $agency
     *
     * @return mixed
     */
    public function get($agency)
    {
        return $agency->admins;
    }

    /**
     * @param $data
     * @param AccountService $account_service
     *
     * @return mixed|null
     * @throws \Exception
     */
    public function store($data, AccountService $account_service)
    {
        DB::beginTransaction();

        try {

            $account = $account_service->store($data + [
                    'type'     => 'agency_admin',
                    'password' => str_random()
                ], new Carbon());

            $admin = $this->repository->create($data + [ 'account_id' => $account->id ]);

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
     */
    public function update($data, $id): void
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
