<?php

namespace Modules\Access\Http\Controllers;

use Carbon\Carbon;
use Modules\Access\Models\AccessType;
use Modules\Program\Models\Program;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Access\Http\Requests\AccessTypeRequest;
use Modules\Access\Http\Services\AccessTypeService;
use Modules\Access\Transformers\AccessTypeTransformer;

class AccessTypeController extends Controller
{
    private $access_type_service;

    /**
     * AccessTypeController constructor.
     * @param AccessTypeService $access_type_service
     */
    public function __construct(AccessTypeService $access_type_service)
    {
        $this->access_type_service = $access_type_service;
    }

    /**
     * @param Program $program
     *
     * @return Fractal
     */
    public function show(Program $program): Fractal
    {
        $access_type = $this->access_type_service->getProgramAccessType($program);

        return fractal($access_type, new AccessTypeTransformer());
    }


    /**
     *
     * Update the specified resource in storage.
     *
     * @param AccessTypeRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(AccessTypeRequest $request, $id): JsonResponse
    {
        $this->access_type_service->update($request->all(), $id);

        return response()->json([ 'message' => 'Access Type Updated Successfully' ]);
    }

}
