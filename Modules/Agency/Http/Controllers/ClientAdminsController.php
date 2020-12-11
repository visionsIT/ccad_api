<?php namespace Modules\Agency\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Account\Http\Services\AccountService;
use Modules\Agency\Http\Requests\ClientsAdminsRequest;
use Modules\Agency\Http\Services\ClientAdminsService;
use Modules\Agency\Http\Services\ClientService;
use Modules\Agency\Transformers\ClientsAdminsTransformer;
use Spatie\Fractal\Fractal;

class ClientAdminsController extends Controller
{
    private $service, $client_service;

    public function __construct(ClientAdminsService $service, ClientService $client_service)
    {
        $this->service        = $service;
        $this->client_service = $client_service;
    }

    /**
     * @param $client_id
     *
     * @return Fractal
     */
    public function index($client_id): Fractal
    {
        $client = $this->client_service->find($client_id);

        $admins = $this->service->get($client);

        return fractal($admins, new ClientsAdminsTransformer());
    }

    /**
     * @param ClientsAdminsRequest $request
     * @param AccountService $account_service
     * @param Carbon $carbon
     * @param $client_id
     *
     * @return Fractal
     * @throws \Exception
     */
    public function store(ClientsAdminsRequest $request, AccountService $account_service, Carbon $carbon, $client_id): Fractal
    {
        $client = $this->client_service->find($client_id);

        $admins = $this->service->store($request->all(), $client, $account_service, $carbon);

        return fractal($admins, new ClientsAdminsTransformer());
    }

    /**
     * Show the specified resource.
     *
     * @param $id
     *
     * @return Fractal
     */
    public function show($client_id, $id): Fractal
    {
        $admin = $this->service->find($id);

        return fractal($admin, new ClientsAdminsTransformer());
    }

    /**
     * @param ClientsAdminsRequest $request
     * @param $client_id
     * @param $id
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(ClientsAdminsRequest $request, $client_id, $id): JsonResponse
    {
        $this->service->update($request->all(), $id);

        return response()->json([ 'message' => 'Data has been successfully updated ' ]);
    }

    /**
     * @param $client_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($client_id, $id): JsonResponse
    {
        $this->service->destroy($id);

        return response()->json([ 'message' => 'Data has been successfully deleted' ]);
    }
}
