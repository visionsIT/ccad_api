<?php namespace Modules\Agency\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Account\Http\Services\AccountService;
use Modules\Account\Models\Account;
use Modules\Agency\Http\Requests\AgenciesAdminsRequest;
use Modules\Agency\Http\Services\AgencyAdminsService;
use Modules\Agency\Http\Services\AgencyService;
use Modules\Agency\Transformers\AgenciesAdminsTransformer;
use Spatie\Fractal\Fractal;

class AgencyAdminsController extends Controller
{
    private $service, $agency_service;

    public function __construct(AgencyAdminsService $service, AgencyService $agency_service)
    {
        $this->service        = $service;
        $this->agency_service = $agency_service;
    }

    /**
     * @param $agency_id
     *
     * @return Fractal
     */
    public function index($agency_id): Fractal
    {
        $agency = $this->agency_service->find($agency_id);

        $admins = $this->service->get($agency);

        return fractal($admins, new AgenciesAdminsTransformer());
    }

    /**
     * @param AgenciesAdminsRequest $request
     * @param AccountService $account_service
     * @param $agency_id
     *
     * @return Fractal
     * @throws \Exception
     */
    public function store(AgenciesAdminsRequest $request, AccountService $account_service, $agency_id): Fractal
    {
        $admins = $this->service->store($request->all() + [ 'agency_id' => $agency_id ], $account_service);

        return fractal($admins, new AgenciesAdminsTransformer());
    }

    /**
     * Show the specified resource.
     *
     * @param $id
     *
     * @return Fractal
     */
    public function show($agency_id, $id): Fractal
    {
        $admin = $this->service->find($id);

        return fractal($admin, new AgenciesAdminsTransformer());
    }

    /**
     * @param AgenciesAdminsRequest $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AgenciesAdminsRequest $request, $agency_id, $id): JsonResponse
    {
        $this->service->update($request->all(), $id);

        return response()->json([ 'message' => 'Data has been successfully updated ' ]);
    }

    /**
     * @param $agency_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($agency_id, $id): JsonResponse
    {
        $this->service->destroy($id);

        return response()->json([ 'message' => 'Data has been successfully deleted' ]);
    }
}
