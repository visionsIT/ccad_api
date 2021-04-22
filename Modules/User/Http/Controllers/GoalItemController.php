<?php namespace Modules\User\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\User\Http\Requests\GoalItemRequest;
use Modules\User\Http\Services\GoalItemService;
use Modules\User\Transformers\GoalItemTransformer;
use Spatie\Fractal\Fractal;
use Helper;
use Illuminate\Http\Request;

class GoalItemController extends Controller
{
    private $service;

    /**
     * GoalItemController constructor.
     *
     * @param GoalItemService $service
     */
    public function __construct(GoalItemService $service)
    {
        $this->service = $service;
		$this->middleware('auth:api');
    }

    /**
     * @param $user_id
     * @param GoalItemRequest $request
     *
     * @return Fractal
     */
    public function store($user_id, Request $request): Fractal
    {

        try{
            $user_id = Helper::customDecrypt($user_id);
            $product_id = $request->product_id;
            $product_id = Helper::customDecrypt($product_id);
            $request['product_id'] = $product_id;
            $rules = [
                'product_id' => 'required|exists:products,id',
            ];

        
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $goal_item = $this->service->store($user_id, $request->all());

            return fractal($goal_item, new GoalItemTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check user_id, product_id and try again.', 'errors' => $th->getMessage()], 402);
        }

    }
        
    /**
     * @param $user_id
     *
     * @return Fractal
     */
    public function getUserGoalItem($user_id)
    {
        try{
            $user_id = Helper::customDecrypt($user_id);
            $goal_item = $this->service->getUserGoalItem($user_id);

            return fractal($goal_item, new GoalItemTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check user_id and try again.', 'errors' => $th->getMessage()], 402);
        }
    }

   /**
     * @param $user_id
     * @param GoalItemRequest $request
     *
     * @return Fractal
     */
    public function removeGoalItem($user_id, Request $request): Fractal
    {

        try{
            $user_id = Helper::customDecrypt($user_id);
            $product_id = $request->product_id;
            $product_id = Helper::customDecrypt($product_id);
            $request['product_id'] = $product_id;
            $rules = [
                'product_id' => 'required|exists:products,id',
            ];

        
            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $goal_item = $this->service->remove($user_id, $request->all());

            return fractal($goal_item, new GoalItemTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check user_id, product_id and try again.', 'errors' => $th->getMessage()], 402);
        }
    }


}
