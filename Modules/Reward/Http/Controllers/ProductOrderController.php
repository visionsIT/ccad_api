<?php namespace Modules\Reward\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Reward\Http\Services\ProductOrderService;
use Modules\Reward\Transformers\ProductTransformer;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductOrder;
use Modules\Reward\Models\ProductDenomination;
use Modules\User\Http\Services\PointService;
use Modules\User\Models\ProgramUsers;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductOrderRequest;
use Modules\Reward\Transformers\ProductOrderTransformer;
use Modules\Reward\Repositories\ProductOrderRepository;
use Modules\User\Models\UsersGroupList;
use Modules\Reward\Exports\OrdersExports;
use Modules\Reward\Exports\OrdersDetailExports;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;
use Modules\User\Models\UsersPoint;
use Helper;
use DB;

class ProductOrderController extends Controller
{
    private $repository, $point_service, $service;

    public function __construct(ProductOrderRepository $repository, PointService $point_service, ProductOrderService $service)
    {
        $this->repository    = $repository;
        $this->point_service = $point_service;
        $this->service       = $service;
		$this->middleware('auth:api');
    }

    /**
     * @return Fractal
     */
    public function index(): Fractal
    {
        $Categorys = $this->repository->getOrders();

        return fractal($Categorys, new ProductOrderTransformer);
    }

    /**
     * @return Fractal
     */
    public function getPendingOrders()
    {
        $orders = $this->service->getPendingOrders();

        return fractal($orders, new ProductOrderTransformer);
    }

    /**
     * @return Fractal
     */
    public function getConfirmedOrders()
    {
        $orders = $this->service->getConfirmedOrders();

        return fractal($orders, new ProductOrderTransformer);
    }

    /**
     * @return Fractal
     */
    public function getCancelledOrders()
    {
        $orders = $this->service->getCancelledOrders();

        return fractal($orders, new ProductOrderTransformer);
    }

    /**
     * @return Fractal
     */
    public function getShippedOrders()
    {
        $orders = $this->service->getShippedOrders();

        return fractal($orders, new ProductOrderTransformer);
    }

    public function search(Request $request)
    {   
        $search = $request->query('q');
        $products = ProductOrder::select('product_orders.*','t1.seen')
            ->leftJoin('products as t1', "t1.id","=","product_orders.product_id")
            ->where(function($query) use ($search){
                $query->where('product_orders.first_name', 'LIKE', "%{$search}%")
                ->orWhere('product_orders.last_name', 'LIKE', "%{$search}%")
                ->orWhere('product_orders.email', 'LIKE', "%{$search}%")
                ->orWhere('t1.name', 'LIKE', "%{$search}%")
                ->orWhereRaw("concat(product_orders.first_name, ' ', product_orders.last_name) LIKE '%{$search}%' ");
            })
            ->distinct()
            ->orderBy('product_orders.created_at', 'DESC')
            ->paginate(12);

        return fractal($products, new ProductOrderTransformer);
    }

    /**
     * @param ProductOrderRequest $request
     *
     * @return Fractal
     */
    public function store(Request $request)
    {   
        
        try{
            $accountID = Helper::customDecrypt($request->account_id);
            $productID = Helper::customDecrypt($request->product_id);
            $denominationID = Helper::customDecrypt($request->value);
            $conversion_rate = Helper::customDecrypt($request->conversion_rate);
            /*$accountID =$request->account_id;
            $productID = $request->product_id;
            $denominationID = $request->value;
            $conversion_rate = $request->conversion_rate;*/
            $country_id = $request->country_id;
            $get_points = ProductDenomination::select('value')->where('id',$denominationID)->first();
            $conversion_rate_final = $conversion_rate * $get_points->value;
            $request['account_id'] = $accountID;
            $request['product_id'] = $productID;
            $request['value'] = $conversion_rate_final;
            $request['denomination_id'] = $denominationID;
            $request['conversion_rate'] = $conversion_rate_final;
            $request['country_id'] = $country_id;

            $rules = [
                'value'      => 'required|numeric',
                'account_id' => 'required|exists:accounts,id',
                'product_id' => 'required|exists:products,id',
                'country_id' => 'required|exists:countries,id',
                'first_name' => 'required',
                'last_name'  => 'required',
                'email'      => 'required|email',
                'phone'      => 'required',
                'address'    => 'required',
                'city'       => 'required',
                'country'    => 'required',
                'is_gift'    => 'required|bool',
                'quantity'   => 'required',
                'conversion_rate'   => 'required',
                'denomination_id'   => 'required|exists:product_denominations,id',
            ];
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $user = ProgramUsers::where('account_id', $request->account_id)->first();

            
            $request['value'] = $conversion_rate_final * $request->quantity;
            
            $current_budget_bal = UsersPoint::select('balance')->where('user_id',$user->id)->latest()->first();
            if($current_budget_bal->balance < $request->value) {
                return response()->json(['message' => "You don't have sufficient balance to place this order.", 'errors' => 'Insufficient Balance'], 402);
            }

            $Category = $this->repository->create($request->all());

            //        //todo fix this later
            //        $user_points = UsersPoint::where([ 'user_id' => $user->id, 'value' => $current ])->first();
            //
            //        $user_points->update([ 'value' => $new ]);

            $data['value']       = $request->value;
            $data['description'] = '';
            $data['product_order_id'] = $Category->id;
            $Category->status = true;
            $this->point_service->store($user, $data, '-');
            $this->service->placeOrder($Category->id);

            return fractal($Category, new ProductOrderTransformer);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check product id,account id and denomination id in value parameter.', 'errors' => $th->getMessage()], 402);
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
    {   try{

            $id = Helper::customDecrypt($id);
            $Category = $this->repository->find($id);

            return fractal($Category, new ProductOrderTransformer);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }   
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
        if(isset($request->account_id)){
            $accountID = Helper::customDecrypt($request->account_id);
            $request['account_id'] = $accountID;
        }

        if(isset($request->product_id)){
            $productID = Helper::customDecrypt($request->product_id);
            $request['product_id'] = $productID;
        }
        $id = Helper::customDecrypt($id);
        $this->repository->update($request->all(), $id);

        return response()->json([ 'message' => 'Category Updated Successfully' ]);
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
        try{
            $id = Helper::customDecrypt($id);
            $this->repository->destroy($id);

            return response()->json([ 'message' => 'Category Trashed Successfully' ]);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }


    public function confirmOrder($id)
    {
        try{
            $id = Helper::customDecrypt($id);
            if ($this->service->confirmOrder($id)) {
                return response()->json([ 'message' => 'The order has been confirmed Successfully' ]);
            }

            return response()->json([ 'message' => 'You cannot change the status any more' ], 400);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

    public function shipOrder($id)
    {   
        try{
            $id = Helper::customDecrypt($id);
            if ($this->service->shipOrder($id)) {
                return response()->json([ 'message' => 'The order has been shipped Successfully' ]);
            }

            return response()->json([ 'message' => 'You cannot change the status any more' ], 400);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }


    public function cancelOrder($id)
    {   
        try{
            $id = Helper::customDecrypt($id);
             
            if ($this->service->cancelOrder($id)) {
                return response()->json([ 'message' => 'The order has been cancelled Successfully' ]);
            }

            return response()->json([ 'message' => 'You cannot change the status any more' ], 400);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function filterByDates(Request $request)
    {
        try {
            $data = $this->service->filterOrders($request->all());
            return $data;
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()]);
        }
    }

    /****************************
    fn to delete all test orders
    *****************************/
    public function deleteTestOrders(Request $request){

        $rules = [
            'group_id' => 'required|exists:roles,id',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $get_group_users = UsersGroupList::select('account_id')->where(['user_group_id'=>$request->group_id])->get();

        if(!empty($get_group_users)){
            $count_deleted = 0;
            foreach($get_group_users as $key=>$value){
                $delete = ProductOrder::where('account_id', $value->account_id)->delete();
                if($delete){
                    $count_deleted++;
                }
            }
            return response()->json(['message'=>'Orders of this group users has been deleted','deleted_orders'=>$count_deleted]);
        }else{
            return response()->json(['message' => 'No user found in this group']);
        }

    }/******fn ends******/

	/***********Start Order Export************/
	
	public function OrdersExport(Request $request)
    {
		$input = $request->all();

		$file = Carbon::now()->timestamp.'-OrdersExport.xlsx';				
		$path = public_path('uploaded/campaign/orders/'.$file); 
		$responsePath = 'uploaded/campaign/orders/'.$file;  
		Excel::store(new OrdersExports($input), 'uploaded/campaign/orders/'.$file, 'real_public');
		return response()->json([
			'file_path' => url($responsePath),
		]);
		
		//return Excel::download(new OrdersExports($input), $file);
    }

	/***********End Order Export************/
	
	/***********Start Order Detail Export************/
	
	public function OrdersDetailExport(Request $request)
    {
		$input = $request->all();

		$file = Carbon::now()->timestamp.'-OrdersDetailExport.xlsx';				
		$path = public_path('uploaded/campaign/orders/'.$file); 
		$responsePath = 'uploaded/campaign/orders/'.$file;  
		Excel::store(new OrdersDetailExports($input), 'uploaded/campaign/orders/'.$file, 'real_public');
		return response()->json([
			'file_path' => url($responsePath),
		]);
		 
		//return Excel::download(new OrdersDetailExports($input), $file);
    }

	/***********End Order Detail Export************/
	
	public function denominationMultipleCountries()
    {
		$d = array();
		$ProductsCountries = DB::Table('products_countries')
								->where('country_id','!=',0)
								->groupBy('product_id')
								->get([DB::raw('count(id) as total_rows'),'product_id','country_id'])
								->toArray();
								
		//echo '<pre>'; print_r($ProductsCountries); die;
		//echo '<pre>';
		if(!empty($ProductsCountries))
		{
			$i = 0;
			foreach($ProductsCountries as $Country)
			{
				ini_set('max_execution_time', -1);
				
				$product_id 	=  $Country->product_id;
				$country_id 	=  $Country->country_id;
				$total_rows 	=  $Country->total_rows;
				if(!empty($product_id) && !empty($country_id))
				{
					if($total_rows != 1)
					{
						$denomiData = DB::Table('product_denominations')
								->where('country_id',$country_id)
								->where('product_id',$product_id)
								->whereNull('deleted_at')
								->get()
								->toArray();
						//echo '<pre>'; print_r($denomiData); die;		
						
						$data = DB::Table('products_countries')
								->where('country_id','!=',0)
								->where('product_id',$product_id)
								->get()
								->toArray();
						//echo '<pre>'; print_r($data); die;
								
						if(!empty($data))
						{
							foreach($data as $row)
							{
								$check = DB::Table('product_denominations')
										->where('country_id',$row->country_id)
										->where('product_id',$row->product_id)
										->whereNull('deleted_at')
										->exists();
								if(!$check)
								{
									$defaultCurrency = DB::Table('point_rate_settings')->select('points')->where('country_id','=',$row->country_id)->get()->first();
									if(empty($defaultCurrency)){
										$getCurrencyPoints = '10';
									}else{
										$getCurrencyPoints = $defaultCurrency->points;
									}
									//echo '<pre>'; print_r($denomiData); die;
									
									if(!empty($denomiData))
									{
										foreach($denomiData as $deno)
										{
											$array = array();
											$array['value'] = $deno->value;
											$array['points'] = $deno->value * $getCurrencyPoints;
											$array['product_id'] = $row->product_id;
											$array['country_id'] = $row->country_id;
											//echo '<pre>'; print_r($array); die; 
											
											ProductDenomination::create($array);
										}
									}
								}
							}
						}
					}
				}

				$i++;
			}
		}
		
		//echo '<pre>'; print_r($d); die;
    }
			
	public function denominationCountries()
    {
		$d = array();
		$ProductsCountries = DB::Table('products_countries')
								->where('country_id','!=',0)
								->groupBy('product_id')
								->get([DB::raw('count(id) as total_rows'),'product_id','country_id'])
								->toArray();
								
		//echo '<pre>'; print_r($ProductsCountries); die;
		if(!empty($ProductsCountries))
		{
			$i = 0;
			foreach($ProductsCountries as $Country)
			{
				ini_set('max_execution_time', -1);
				
				$product_id 	=  $Country->product_id;
				$country_id 	=  $Country->country_id;
				$total_rows 	=  $Country->total_rows;
				if(!empty($product_id) && !empty($country_id))
				{
					//if($total_rows == 1)
						DB::Table('product_denominations')->where(array( 'product_id' => $product_id))->update(array( 'country_id' => $country_id));
					//else
					//	$d[] = $Country->product_id;
				}

				$i++;
			}
		}
		
		//echo '<pre>'; print_r($d); die;
    }
	
	public function mappProductOrderData()
    {
		$productData = ProductOrder::all();
		//echo '<pre>'; print_r($productData->toArray()); die;
		if(!empty($productData))
		{
			foreach($productData as $prod)
			{
				$UserData = DB::Table('program_users')->select('country_id')->where('account_id','=',$prod->account_id)->get()->first();
				$UserCountryID = (!empty($UserData) && isset($UserData->country_id) && !empty($UserData->country_id)) ? $UserData->country_id : false;
				
				//echo $prod->account_id ."   ======  ". $UserCountryID."<br>";
				
				$DenoData = DB::Table('product_denominations')->select('value')->where('id','=',$prod->denomination_id)->get()->first();
				$DenoValue = (!empty($DenoData) && isset($DenoData->value) && !empty($DenoData->value)) ? $DenoData->value : false;
				
				$perUnitValue = 0;
				if(!empty($prod->value) && !empty($prod->quantity))
					$perUnitValue = $prod->value / $prod->quantity;
				
				$rate = 0;	
				if(!empty($perUnitValue) && !empty($DenoValue))
					$rate = round($perUnitValue / $DenoValue,2);
					
				$array = array();
				$array['country_id'] = $UserCountryID;
				$array['conversion_rate'] = $rate;
				
				ProductOrder::where(array('id' => $prod->id))->update($array);
			}
			
			echo 'done';
		}
    }		

}
