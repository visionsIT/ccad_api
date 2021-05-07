<?php namespace Modules\Agency\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Agency\Repositories\ClientRepository;
use Modules\Agency\Http\Requests\ClientRequest;
use Modules\Agency\Http\Services\AgencyService;
use Modules\Agency\Transformers\ClientTransformer;
use Spatie\Fractal\Fractal;

class ClientController extends Controller
{
    private $repository;

    public function __construct(ClientRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $clients = $this->repository->get();

        return fractal($clients, new ClientTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ClientRequest $request
     *
     * @return Fractal
     */
    public function store(ClientRequest $request)
    {
        $client = $this->repository->create($request->except('catalogues_id'));

        //todo handle if this throw exception it must a db transaction
        $this->repository->attachCataloguesToClient($client, $request->catalogues_id);

        return fractal($client, new ClientTransformer);
    }

    /**
     * Show the specified resource.
     *
     * @param $id
     *
     * @return Fractal
     */
    public function show($id): Fractal
    {
        $client = $this->repository->find($id);

        return fractal($client, new ClientTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param ClientRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ClientRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->except('catalogues_id'), $id);

        $this->repository->syncCataloguesToClient($this->repository->find($id), $request->catalogues_id);

        return response()->json([ 'message' => 'Client Updated Successfully' ]);
    }

    /**
     *
     * Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $client = $this->repository->find($id);

        $ids = $client->catalogues()->pluck('id');

        $this->repository->detachCataloguesToClient($client, $ids);

        $this->repository->destroy($id);

        return response()->json([ 'message' => 'Client Trashed Successfully' ]);
    }


    public function agencyClients(AgencyService $agency_service, $agency_id)
    {
        $clients = $agency_service->getClients($agency_id);

        return fractal($clients, new ClientTransformer);
    }
}
