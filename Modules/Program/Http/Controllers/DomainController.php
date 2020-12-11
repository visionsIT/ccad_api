<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Program\Http\Requests\DomainRequest;
use Modules\Program\Http\Services\DomainService;
use Modules\Program\Http\Services\ProgramService;
use Modules\Program\Transformers\DomainsTransformer;
use Spatie\Fractal\Fractal;

class DomainController extends Controller
{
    private $service, $program_service;

    public function __construct(ProgramService $program_service, DomainService $service)
    {
        $this->service         = $service;
        $this->program_service = $program_service;
    }


    /**
     * @param $program_id
     *
     * @return Fractal
     */
    public function index($program_id): Fractal
    {
        $domains = $this->program_service->find($program_id)->domains;

        return fractal($domains, new DomainsTransformer());
    }

    /**
     * @param $program_id
     * @param DomainRequest $request
     *
     * @return Fractal
     */
    public function store($program_id, DomainRequest $request): Fractal
    {
        $program = $this->program_service->find($program_id);

        $domain = $this->service->store($program, $request->all());

        return fractal($domain, new DomainsTransformer());
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
        $program = $this->service->show($id);

        return fractal($program, new DomainsTransformer());
    }

    /**
     * @param DomainRequest $request
     * @param $program_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(DomainRequest $request, $program_id, $id): JsonResponse
    {
        $this->service->update($request->all(), $id);

        return response()->json([ 'message' => 'Program\'s Domain Updated Successfully' ]);
    }


    /**
     * @param $program_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($program_id, $id): JsonResponse
    {
        $this->service->destroy($id);

        return response()->json([ 'message' => 'Program\'s Domain Trashed Successfully' ]);
    }

}
