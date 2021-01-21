<?php

namespace Modules\Reward\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductCatalogRequest;
use Modules\Reward\Transformers\ProductCatalogTransformer;
use Modules\Reward\Repositories\ProductCatalogRepository;
use Helper;

class ProductCatalogController extends Controller
{
    private $repository;

    public function __construct(ProductCatalogRepository $repository)
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

        if(isset($_GET['country_id'])){
            define('COUNTRY_CODE', $_GET['country_id']);
        }else{
            define('COUNTRY_CODE', '');
        }
        
        $Catalogs = $this->repository->get()->sortBy('name');
        return fractal($Catalogs, new ProductCatalogTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductCatalogRequest $request
     * @return Fractal
     */
    public function store(ProductCatalogRequest $request)
    {
        $Catalog = $this->repository->create($request->all());

        return fractal($Catalog, new ProductCatalogTransformer);
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

    /**
     *
     * Update the specified resource in storage.
     *
     * @param ProductCatalogRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ProductCatalogRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->all(), $id);

        return response()->json(['message' => 'Catalog Updated Successfully']);
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

        return response()->json(['message' => 'Catalog Trashed Successfully']);
    }

    public function updateCatalogStatus(Request $request) {
        try {
            $rules = [
                'id' => 'required|integer|exists:products,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $catalog = $this->repository->find($request->id);
            $catalog->status = $request->status;
            $catalog->save();

            return response()->json(['message' => 'Category status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function updateCatalog(Request $request, $id): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string|unique:product_catalogs,name,'.$id
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $this->repository->update($request->all(), $id);

            return response()->json(['message' => 'Catalog Updated Successfully']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }
}
