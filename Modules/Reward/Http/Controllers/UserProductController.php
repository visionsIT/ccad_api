<?php

namespace Modules\Reward\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductRequest;
use Modules\Reward\Transformers\ProductTransformer;
use Modules\Reward\Repositories\ProductRepository;



class UserProductController extends Controller
{
    private $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $Programs = $this->repository->get();

        return fractal($Programs, new ProductTransformer);
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
        $Program = $this->repository->find($id);

        return fractal($Program, new ProductTransformer);
    }

}
