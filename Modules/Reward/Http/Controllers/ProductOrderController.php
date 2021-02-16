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

class ProductOrderController extends Controller
{
    private $repository, $point_service, $service;

    public function __construct(ProductOrderRepository $repository, PointService $point_service, ProductOrderService $service)
    {
        $this->repository    = $repository;
        $this->point_service = $point_service;
        $this->service       = $service;
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

    /**
     * @param ProductOrderRequest $request
     *
     * @return Fractal
     */
    public function store(Request $request)
    {
        $get_points = ProductDenomination::select('points')->where('id',$request->value)->first();

        $request['value'] = $get_points->points;
        $request['denomination_id'] = $request->value;

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
            'quantity'   => 'required',
            'denomination_id'   => 'required|exists:product_denominations,id',
        ];
        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $user = ProgramUsers::where('account_id', $request->account_id)->first();
        
        $request['value'] = $get_points->points * $request->quantity;

        $request['value'] = $get_points->points * $request->quantity;

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
        $this->repository->destroy($id);

        return response()->json([ 'message' => 'Category Trashed Successfully' ]);
    }


    public function confirmOrder($id)
    {
        if ($this->service->confirmOrder($id)) {
            return response()->json([ 'message' => 'The order has been confirmed Successfully' ]);
        }

        return response()->json([ 'message' => 'You cannot change the status any more' ], 400);
    }

    public function shipOrder($id)
    {
        if ($this->service->shipOrder($id)) {
            return response()->json([ 'message' => 'The order has been shipped Successfully' ]);
        }

        return response()->json([ 'message' => 'You cannot change the status any more' ], 400);
    }


    public function cancelOrder($id)
    {
        if ($this->service->cancelOrder($id)) {
            return response()->json([ 'message' => 'The order has been cancelled Successfully' ]);
        }

        return response()->json([ 'message' => 'You cannot change the status any more' ], 400);
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

}
