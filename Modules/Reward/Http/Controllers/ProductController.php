<?php

namespace Modules\Reward\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Reward\Http\Services\ProductService;
use Modules\Reward\Models\ProductDenomination;
use Modules\Reward\Models\ProductsAccountsSeen;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductRequest;
use Modules\Reward\Transformers\ProductTransformer;
use Modules\Reward\Transformers\ProductsTransformer;
use Modules\Reward\Repositories\ProductRepository;
use Illuminate\Database;
use Modules\Reward\Transformers\BrandTransformer;
use Modules\Reward\Models\ProductBrand;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Reward\Exports\RewardsExports;
use Modules\CommonSetting\Models\PointRateSettings;
use Modules\Reward\Models\ProductsCountries;
use Modules\User\Models\ProgramUsers;
use Modules\User\Http\Requests\ProgramUsersRequest;
use Throwable;
use DB;
class ProductController extends Controller
{
    private $repository, $service;

    public function __construct(ProductRepository $repository, ProductService $service)
    {
        $this->repository = $repository;
        $this->service    = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {   
       
        $products = $this->repository->paginate(12);

        return fractal($products, new ProductTransformer);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest $request
     *
     * @return Fractal
     */
    public function store(ProductRequest $request)
    {
        $product = $this->repository->create($request->all());
        return fractal($product, new ProductTransformer);
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
        
        $product = $this->repository->find($id);
        $useraccount = \Auth::user();
        $accountID =  $useraccount->id;
        
        ProductsAccountsSeen::create([
                'account_id' => $accountID,
                'product_id' => $product->id
            ]);

        return fractal($product, new ProductsTransformer);
    }


    /**
     *
     * Update the specified resource in storage.
     *
     * @param ProductRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ProductRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->all(), $id);

        return response()->json([ 'message' => 'Product Updated Successfully' ]);
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

        return response()->json([ 'message' => 'Product Trashed Successfully' ]);
    }

    public function search(Request $request)
    {
        $products = $this->service->search($request->query('keyword'));

        return fractal($products, new ProductTransformer);
    }

    public function searchAdvance(Request $request)
    {
        $keyword = $request->query('keyword') ?? "";
        $categoryId = $request->query('categoryId') ?? 0;
        $subcategoryId = $request->query('subcategoryId') ?? 0;
        $minValue = $request->query('minValue') ?? 0;
        $maxvalue = $request->query('maxValue') ?? 999999999;
        $brandId = $request->query('brandId') ?? '';
        $eligibility = $request->query('eligibility') ?? 0;
        $eligibilityChecked = $request->query('eligibilityChecked') ?? 0;
        $adminCall = ($request->query('acc')==1)?$request->query('acc'):'';
        $order = $request->query('order') ?? '';
        $col = $request->query('col') ?? '';
        $pid = $request->query('pid') ?? '';
        if($eligibility>0){
            $maxvalue = $eligibility;
        }
        // if($eligibilityChecked > 0 && $eligibilityChecked != ""){
        //     $maxvalue = $eligibility/10;
        // }

        $country_id = $request->query('country_id') ?? "";

        $_SESSION['minValue'] = $minValue;
        $_SESSION['maxValue'] = $maxvalue;

        $denominationsList = ProductDenomination::select('product_id')->whereRaw('CAST(points AS DECIMAL(10,2)) >= ' . $minValue)->whereRaw('CAST(points AS DECIMAL(10,2)) <= ' . $maxvalue)->groupBy('product_id')->get()->all();

        $productIds = [];
        foreach($denominationsList as $deno){
            $productIds[] = $deno->product_id;
        }
        $brandIds = '';
        if($brandId != ''){
            $brandIds = explode('_', $brandId);
        }

        $products =  $this->repository->searchAdvance($keyword, $categoryId, $minValue, $maxvalue, $subcategoryId, $productIds, $brandIds, 'searchAd', $adminCall, $order, $col, $country_id);
        if($pid != '' && $pid == 1){
            $param = [
                'search' => $keyword,
                'column' => ($col)?$col:'id',
                'order' => ($order)?$order:'desc',
            ];

            $file = (Carbon::now())->toDateString().'-AllRewardsData.xlsx';
            $path = 'uploaded/'.$pid.'/users/csv/exported/'.$file;
            $responsePath = "/export-file/{$pid}/{$file}";
            Excel::store(new RewardsExports($param), $path);
            return response()->json([
                'file_path' => url($responsePath),
            ]);
        } else {

            return fractal($products, new ProductTransformer);
        }
    }
    public function searchAdvance1(Request $request)
    {
        $keyword = $request->query('keyword') ?? "";
        $categoryId = $request->query('categoryId') ?? 0;
        $subcategoryId = $request->query('subcategoryId') ?? 0;
        $minValue = $request->query('minValue') ?? 0;
        $maxvalue = $request->query('maxValue') ?? 999999999;
        $brandId = $request->query('brandId') ?? '';
        $eligibility = $request->query('eligibility') ?? 0;
        $eligibilityChecked = $request->query('eligibilityChecked') ?? 0;
        $adminCall = ($request->query('acc')==1)?$request->query('acc'):'';
        $order = $request->query('order') ?? '';
        $col = $request->query('col') ?? '';
        $pid = $request->query('pid') ?? '';
        if($eligibility>0){
            $maxvalue = $eligibility;
        }
        // if($eligibilityChecked > 0 && $eligibilityChecked != ""){
        //     $maxvalue = $eligibility/10;
        // }

        $_SESSION['minValue'] = $minValue;
        $_SESSION['maxValue'] = $maxvalue;

        $denominationsList = ProductDenomination::select('product_id')->whereRaw('CAST(points AS DECIMAL(10,2)) >= ' . $minValue)->whereRaw('CAST(points AS DECIMAL(10,2)) <= ' . $maxvalue)->groupBy('product_id')->get()->all();

        $productIds = [];
        foreach($denominationsList as $deno){
            $productIds[] = $deno->product_id;
        }
        $brandIds = '';
        if($brandId != ''){
            $brandIds = explode('_', $brandId);
        }

        $products =  $this->repository->searchAdvance($keyword, $categoryId, $minValue, $maxvalue, $subcategoryId, $productIds, $brandIds, 'searchAd', $adminCall, $order, $col);
        if($pid != '' && $pid == 1){
            $param = [
                'search' => $keyword,
                'column' => ($col)?$col:'id',
                'order' => ($order)?$order:'desc',
            ];

            $file = (Carbon::now())->toDateString().'-AllRewardsData.xlsx';
            $path = 'uploaded/'.$pid.'/users/csv/exported/'.$file;
            $responsePath = "/export-file/{$pid}/{$file}";
            Excel::store(new RewardsExports($param), $path);
            return response()->json([
                'file_path' => url($responsePath),
            ]);
        } else {
            
            return fractal($products, new ProductTransformer);
        }
    }

    public function getBrandsByCategory(Request $request){
        $categoryId = $request->query('categoryId') ?? 0;
        $products =  $this->repository->searchProductsByCategory($categoryId);
        return fractal($products, new BrandTransformer);
    }

    public function fetchMaxPoint(): JsonResponse {
        try {
            $result = ProductDenomination::max('points');
            $response["maxPoint"] = $result;
            $response["message"] = "Maximum Points value";
        } catch (Throwable $error) {
            $response["maxPoint"] = null;
            $response["message"] = "Something went wrong";
            $response["error"] = $error;
        }

        return response()->json($response);
    }

   public function addProduct(Request $request, $id=null) {

        try {
            $rules = [
                'name' => 'required',
                'brand_name' => 'required',
                'category' => 'required|integer|exists:product_catalogs,id',
                //'sub_category_id' => 'required|integer|exists:product_categories,id',
                'type' => 'required',
                //'validity' => 'required',
                'description' => 'required',
                //'term_condition' => 'required',
                'image' => 'required|file||mimes:jpeg,png,jpg',
                'action' => 'required',
                'currency_id' => 'required|integer|exists:currencies,id',
                
            ];

            if($request->action && $request->action !== '') {
                unset($rules['action']);
            }

            if($id !== null && $request->action === 'update' && $request->fileName !== '') {
                unset($rules['image']);
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $brand = ProductBrand::updateOrCreate([
                'name' => $request->brand_name,
            ]);

            $imgName = !$request->hasFile('image') && $request->action === 'update' ? $request->fileName : '';
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $imgName = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $destinationPath = public_path('storage/products_img/');
                $file->move($destinationPath, $imgName);
            }

            $productData = [
                'sku' => ($request->sku!='')?$request->sku:'',
                'name' => $request->name,
                'image' => $imgName,
                'type' => $request->type,
                'validity' => $request->validity,
                'description' => $request->description,
                'terms_conditions' => $request->term_condition,
                'quantity' => 'available',
                'value' => 0,
                'base_price' => 0,
                'likes' => 0,
                'model_number' => '',
                'min_age' => '',
                'catalog_id' => $request->category,
                'category_id' => $request->sub_category_id,
                'brand_id' => $brand->id,
            ];

            $defaultCurrency = PointRateSettings::select('points')->where('currency_id','=',$request->currency_id)->first();
            if(empty($defaultCurrency)){
                $getCurrencyPoints = '10';
            }else{
                $getCurrencyPoints = $defaultCurrency->points;
            }
            
            // Currencies
            

            if($id !== null && $request->action === 'update') {

                $this->service->update($productData, $id);

                DB::table('products')
                    ->where('id', $id)
                    ->update(['currency_id' => $request->currency_id]);

                /*** Product denomination Update *****/
                $denomi = explode(',', $request->denominations);
                $productDenoData = ProductDenomination::where('product_id', $id)->get();
                if($productDenoData){
                    $deletedRows = ProductDenomination::where('product_id', $id)->delete();
                    /*$productDenoDataf = $productDenoData->toArray();
                    foreach ($productDenoDataf as $key => $value_d) {
                        
                        $denom_Value = $value_d['value'];

                        if (!in_array($denom_Value, $denomi)){
                            $deletedRows = ProductDenomination::where('id', $value_d['id'])->delete();
                        }
                        

                    }*/

                }
                
                foreach($denomi as $denoValue){
                    /*ProductDenomination::updateOrCreate([
                        'value' => $denoValue,
                        'points' => (int)$denoValue*(int)$getCurrencyPoints,
                        'product_id' => $id,
                    ]);*/

                    ProductDenomination::create([
                            'value' => $denoValue,
                            'points' => (((float)$denoValue)*((float)$getCurrencyPoints)),
                            'product_id' => $id,
                        ]);
                }

                /*** Product country Update *****/

                $country_id = explode(',', $request->country_id);

                $productCountriesData = ProductsCountries::where('product_id', $id)->get();
                if($productCountriesData){
                    $productCountriesDataf = $productCountriesData->toArray();
                    foreach ($productCountriesDataf as $key => $value_c) {
                        
                        $country_id_data = $value_c['country_id'];
                        $deletedRows = ProductsCountries::where('id', $value_c['id'])->delete();
                        
                    }

                }
              
               if(!empty($country_id)){
                    foreach($country_id as $countyid){
                        
                        ProductsCountries::create([
                            'product_id'    => $id,
                            'country_id'    => $countyid,
                        ]);
                    }
                }

                $str = "Product has been updated successfully.";

            } else if($id === null && $request->action === 'create') {
                $product = $this->repository->create($productData);
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['currency_id' => $request->currency_id]);
                $denomi = explode(',', $request->denominations);
                foreach($denomi as $denoValue){
                    ProductDenomination::updateOrCreate([
                        'value' => $denoValue,
                        'points' => (((float)$denoValue)*((float)$getCurrencyPoints)),
                        'product_id' => $product->id,
                    ]);
                }

                /*** Add country ID[Multiple] ****/
                
                $country_id = explode(',', $request->country_id);
                if(!empty($country_id)){
                    foreach($country_id as $countyid){
                       
                        ProductsCountries::create([
                            'product_id'    => $product->id,
                            'country_id'    => $countyid,
                        ]);
                    }
                }
                
                $str = "Product has been added successfully.";
            }
            return response()->json([ 'message' => $str ]);
        }  catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

    /*public function updateProduct(Request $request, $id) {
        try {
            $rules = [
                'name' => 'required',
                'brand_name' => 'required',
                'category' => 'required|integer|exists:product_catalogs,id',
                'sub_category_id' => 'required|integer|exists:product_categories,id',
                'type' => 'required',
                'validity' => 'required',
                'description' => 'required',
                'term_condition' => 'required',
                'image' => 'required|file||mimes:jpeg,png,jpg',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $brand = ProductBrand::updateOrCreate([
                'name' => $request->brand_name,
            ]);

            $imgName = $request->fileName !== '' ? $request->fileName : '';

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $imgName = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $destinationPath = public_path('storage/products_img/');
                $file->move($destinationPath, $imgName);
            }

            $productData = [
                'sku' => ($request->sku!='')?$request->sku:'',
                'name' => $request->name,
                'image' => $imgName,
                'type' => $request->type,
                'validity' => $request->validity,
                'description' => $request->description,
                'terms_conditions' => $request->term_condition,
                'quantity' => 'available',
                'value' => 0,
                'base_price' => 0,
                'likes' => 0,
                'model_number' => '',
                'min_age' => '',
                'catalog_id' => $request->category,
                'category_id' => $request->sub_category_id,
                'brand_id' => $brand->id,
                'status' => '0',
            ];

            $this->service->update($productData, $id);

            $denomi = explode(',', $request->denominations);
            foreach($denomi as $denoValue){
                ProductDenomination::updateOrCreate([
                    'value' => $denoValue,
                    'points' => ((int)$denoValue*10),
                    'product_id' => $id,
                ]);
            }

            return response()->json([ 'message' => 'Product data has been updated successfully' ]);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    } */

    public function updateProductStatus(Request $request) {
        try {
            $rules = [
                'id' => 'required|integer|exists:products,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $product = $this->repository->find($request->id);
            $product->status = $request->status;
            $product->save();

            return response()->json(['message' => 'Product status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }
}
