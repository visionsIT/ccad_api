<?php

namespace Modules\Nomination\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\NominationTypeRequest;
use Modules\Nomination\Transformers\NominationTypeTransformer;
use Modules\Nomination\Repositories\NominationTypeRepository;
use Modules\Nomination\Repositories\ValueSetRepository;

class NominationType2Controller extends Controller
{
    private $repository;

    public function __construct(NominationTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index($value_set_id): Fractal
    {

        //$order = $this->repository->get();
        //return fractal($order, new NominationTypeTransformer);
        $NTypes = $this->repository->value_set_types($value_set_id);       
        return fractal($NTypes, new NominationTypeTransformer);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param NominationTypeRequest $request
     * @return Fractal
     */
    public function store(NominationTypeRequest $request)
    {
        $Category = $this->repository->create($request->all());

        return fractal($Category, new NominationTypeTransformer);
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
        $Category = $this->repository->find($id);

        return fractal($Category, new NominationTypeTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param NominationTypeRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(NominationTypeRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->all(), $id);

        return response()->json(['message' => 'Category Updated Successfully']);
    }

    /**
     *
     *  Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json(['message' => 'Category Trashed Successfully']);
    }
}
