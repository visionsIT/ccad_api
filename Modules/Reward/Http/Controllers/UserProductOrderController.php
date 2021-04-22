<?php

namespace Modules\Reward\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductOrderRequest;
use Modules\Reward\Transformers\ProductOrderTransformer;
use Modules\Reward\Repositories\ProductOrderRepository;

class UserProductOrderController extends Controller
{
    private $repository;

    public function __construct(ProductOrderRepository $repository)
    {
        $this->repository = $repository;
		$this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $order = $this->repository->get();
        return fractal($order, new ProductOrderTransformer);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function myorders($account_id): Fractal
    {
        $order = $this->repository->UserOrders($account_id);
        return fractal($order, new ProductOrderTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductOrderRequest $request
     * @return Fractal
     */
    public function store(ProductOrderRequest $request)
    {
        $Category = $this->repository->create($request->all());

        return fractal($Category, new ProductOrderTransformer);
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

        return fractal($Category, new ProductOrderTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param ProductOrderRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ProductOrderRequest $request, $id): JsonResponse
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
