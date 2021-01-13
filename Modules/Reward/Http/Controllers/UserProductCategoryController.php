<?php

namespace Modules\Reward\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Routing\Controller;
use Modules\Reward\Transformers\ProductCategoryTransformer;
use Modules\Reward\Repositories\ProductCategoryRepository;

class UserProductCategoryController extends Controller
{
    private $repository;

    public function __construct(ProductCategoryRepository $repository)
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
        $Categorys = $this->repository->get();

        return fractal($Categorys, new ProductCategoryTransformer);
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

        return fractal($Category, new ProductCategoryTransformer);
    }

}
