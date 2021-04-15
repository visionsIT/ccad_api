<?php

namespace Modules\Reward\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\SubProductRequest;
use Modules\Reward\Transformers\SubProductTransformer;
use Modules\Reward\Repositories\SubProductRepository;



class SubProductController extends Controller
{
    private $repository;

    public function __construct(SubProductRepository $repository)
    {
        $this->repository = $repository;
		$this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $SubPrograms = $this->repository->get();

        return fractal($SubPrograms, new SubProductTransformer);
    }


    /**
     * Display a listing of the resource.
     * @return \Spatie\Fractal\Fractal
     */
    public function sub(Request $product_id): Fractal
    {
        $SubPrograms = $this->repository->where("product_id",$product_id);

        return fractal($SubPrograms, new SubProductTransformer);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param SubProductRequest $request
     * @return Fractal
     */
    public function store(SubProductRequest $request)
    {
        $SubProgram = $this->repository->create($request->all());

        return fractal($SubProgram, new SubProductTransformer);
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
        $SubProgram = $this->repository->find($id);

        return fractal($SubProgram, new SubProductTransformer);
    }



    /**
     *
     * Update the specified resource in storage.
     *
     * @param SubProductRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(SubProductRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->all(), $id);

        return response()->json(['message' => 'SubProduct Updated Successfully']);
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

        return response()->json(['message' => 'SubProduct Trashed Successfully']);
    }


}
