<?php

namespace Modules\Reward\Http\Controllers;

use Spatie\Fractal\Fractal;
use Illuminate\Routing\Controller;
use Modules\Reward\Transformers\ProductCatalogTransformer;
use Modules\Reward\Repositories\ProductCatalogRepository;

class UserProductCatalogController extends Controller
{
    private $repository;

    public function __construct(ProductCatalogRepository $repository)
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
        $Catalogs = $this->repository->get();

        return fractal($Catalogs, new ProductCatalogTransformer);
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
        $Catalog = $this->repository->find($id);

        return fractal($Catalog, new ProductCatalogTransformer);
    }

}
