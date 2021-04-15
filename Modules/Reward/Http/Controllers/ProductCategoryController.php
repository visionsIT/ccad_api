<?php

namespace Modules\Reward\Http\Controllers;

use Modules\Reward\Http\Requests\ProductCatalogRequest;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductCategoryRequest;
use Modules\Reward\Http\Requests\ProductRequest;
use Modules\Reward\Transformers\ProductCategoryTransformer;
use Modules\Reward\Transformers\ProductTransformer;
use Modules\Reward\Repositories\ProductCategoryRepository;
use Modules\Reward\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Http\response;
use DB;
class ProductCategoryController extends Controller
{
    private $repository;
    private $prod_repository;

    public function __construct(ProductCategoryRepository $repository,ProductRepository $prod_repository)
    {
        $this->repository = $repository;
        $this->prod_repository = $prod_repository;
		$this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $Categorys = $this->repository->MainCategory();

        return fractal($Categorys, new ProductCategoryTransformer);
    }

//    /**
//     * Display a listing of the resource.
//     *
//     * @return \Spatie\Fractal\Fractal
//     */
//    public function sub_prod($category_id): Fractal
//    {
//        $Product = $this->prod_repository->sub_prod($category_id);
//
//        return fractal($Product, new ProductTransformer);
//    }

    /**
     * @param $catalog_id
     *
     * @return Fractal
     */
    public function sub_prod($catalog_id): Fractal
    {
        $Product = $this->prod_repository->sub_prod($catalog_id);

        return fractal($Product, new ProductTransformer);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param ProductCategoryRequest $request
     * @return Fractal
     */
    public function store(ProductCategoryRequest $request)
    {
        $check_data = DB::table('product_categories')->where($request->all())->get();
    
        if(count($check_data) == 0){
            $Category = $this->repository->create($request->all());

            return fractal($Category, new ProductCategoryTransformer);
        }else{
            return response()->json(['message' => 'Already exists','status'=>'error']);
        }
        
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

    /**
     *
     * Update the specified resource in storage.
     *
     * @param ProductCategoryRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ProductCategoryRequest $request, $id): JsonResponse
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

    public function updateCategoryStatus(Request $request) {
        try {
            $rules = [
                'id' => 'required|integer|exists:product_categories,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $category = $this->repository->find($request->id);
            $category->status = $request->status;
            $category->save();

            return response()->json(['message' => 'Sub Category status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function updateSubCategory(Request $request, $id): JsonResponse
    {
        try {
            $rules = [
                'name' => 'required|string',
                'catalog' => 'required|integer|exists:product_catalogs,id'
            ];
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $check_data = DB::table('product_categories')->where($request->all())->where('id','!=',$id)->get();

            if(count($check_data) == 0){
                $this->repository->update($request->all(), $id);

                return response()->json(['message' => 'Sub Category Updated Successfully']);
            }else{
                return response()->json(['message' => 'Already exists','status'=>'error']);
            }

            
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }
}
