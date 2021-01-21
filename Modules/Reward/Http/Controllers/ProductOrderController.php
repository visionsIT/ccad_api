<?php namespace Modules\Reward\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Reward\Http\Services\ProductOrderService;
use Modules\Reward\Transformers\ProductTransformer;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductDenomination;
use Modules\User\Http\Services\PointService;
use Modules\User\Models\ProgramUsers;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reward\Http\Requests\ProductOrderRequest;
use Modules\Reward\Transformers\ProductOrderTransformer;
use Modules\Reward\Repositories\ProductOrderRepository;
use Helper;

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
        $Categorys = $this->repository->paginate(12);

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
            $get_points = ProductDenomination::select('points')->where('id',$denominationID)->first();
            $request['account_id'] = $accountID;
            $request['product_id'] = $productID;
            $request['value'] = $get_points->points;
           
            $rules = [
                'value'      => 'required|numeric',
                'account_id' => 'required|exists:accounts,id',
                'product_id' => 'required|exists:products,id',
                'first_name' => 'required',
                'last_name'  => 'required',
                'email'      => 'required|email',
                'phone'      => 'required',
                'address'    => 'required',
                'city'       => 'required',
                'country'    => 'required',
                'is_gift'    => 'required|bool',
            ];
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
            $user = ProgramUsers::where('account_id', $request->account_id)->first();

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
           
        }catch (\Throwable $th) {
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
    public function show($id)
    {

        try{
            $id = Helper::customDecrypt($id);
            $Category = $this->repository->find($id);

            return fractal($Category, new ProductOrderTransformer);
        }catch (\Throwable $th) {
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
    public function update(Request $request, $id): JsonResponse
    {

        try{
            if(isset($request->account_id)){
                $accountID = Helper::customDecrypt($request->account_id);
                $request['account_id'] = $accountID;
            }

            if(isset($request->product_id)){
                $productID = Helper::customDecrypt($request->product_id);
                $request['product_id'] = $productID;
            }
            $denominationID = Helper::customDecrypt($request->value);
            $get_points = ProductDenomination::select('points')->where('id',$denominationID)->first();
            $request['value'] = $get_points->points;

            $rules = [
                'value'      => 'required|numeric',
                'account_id' => 'required|exists:accounts,id',
                'product_id' => 'required|exists:products,id',
                'first_name' => 'required',
                'last_name'  => 'required',
                'email'      => 'required|email',
                'phone'      => 'required',
                'address'    => 'required',
                'city'       => 'required',
                'country'    => 'required',
                'is_gift'    => 'required|bool',
            ];
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $id = Helper::customDecrypt($id);
            $this->repository->update($request->all(), $id);

            return response()->json([ 'message' => 'Category Updated Successfully' ]);
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check account_id, product id and denomination id.', 'errors' => $th->getMessage()], 402);
        }

        
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
        }catch (\Throwable $th) {
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
        }catch (\Throwable $th) {
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
        }catch (\Throwable $th) {
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
        }catch (\Throwable $th) {
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

}
