<?php namespace Modules\Agency\Http\Repositories;

use App\Repositories\Repository;
use DB;
use Modules\Agency\Models\ClientsAdmin;

class ClientAdminsRepository extends Repository
{
    protected $modeler = ClientsAdmin::class;

    /**
     * @param $data
     * @param $id
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function update($data, $id)
    {
        DB::beginTransaction();

        try {

            $client_admin = $this->find($id);

            $client_admin->account()->update($data);

            $client_admin->update($data);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();

            throw $e;
        }
    }

}
