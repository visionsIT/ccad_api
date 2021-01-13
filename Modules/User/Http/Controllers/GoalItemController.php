<?php namespace Modules\User\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\User\Http\Requests\GoalItemRequest;
use Modules\User\Http\Services\GoalItemService;
use Modules\User\Transformers\GoalItemTransformer;
use Spatie\Fractal\Fractal;

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
    public function store($user_id, GoalItemRequest $request): Fractal
    {
        $goal_item = $this->service->store($user_id, $request->all());


        return fractal($goal_item, new GoalItemTransformer());
    }

    /**
     * @param $user_id
     *
     * @return Fractal
     */
    public function getUserGoalItem($user_id): Fractal
    {
        $goal_item = $this->service->getUserGoalItem($user_id);

        return fractal($goal_item, new GoalItemTransformer());
    }

   /**
     * @param $user_id
     * @param GoalItemRequest $request
     *
     * @return Fractal
     */
    public function removeGoalItem($user_id, GoalItemRequest $request): Fractal
    {
        $goal_item = $this->service->remove($user_id, $request->all());

        return fractal($goal_item, new GoalItemTransformer());
        //return fractal($goal_item, new GoalItemTransformer());
    }


}
