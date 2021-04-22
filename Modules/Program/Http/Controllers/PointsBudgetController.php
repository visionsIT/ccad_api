<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Program\Http\Requests\BudgetRequest;
use Modules\Program\Http\Requests\BudgetStatusRequest;
use Modules\Program\Http\Services\PointsBudgetService;
use Modules\Program\Http\Services\ProgramService;
use Modules\Program\Transformers\PointsBudgetTransformer;

class PointsBudgetController extends Controller
{
    private $service, $program_service;

    public function __construct(ProgramService $program_service, PointsBudgetService $service)
    {
        $this->service         = $service;
        $this->program_service = $program_service;
		$this->middleware('auth:api');
    }

    public function index($program_id)
    {
        $points_budget = $this->program_service->find($program_id)->points_budget;

        return fractal($points_budget, new PointsBudgetTransformer());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BudgetRequest $request
     * @param $program_id
     *
     * @return JsonResponse
     */
    public function update(BudgetRequest $request, $program_id): JsonResponse
    {
        $this->service->update($request->all(), $program_id);

        return response()->json([ 'message' => 'Program\'s Budget Updated Successfully' ]);
    }

    /**
     * @param BudgetStatusRequest $request
     * @param $program_id
     *
     * @return JsonResponse
     */
    public function changeBudgetStatus(BudgetStatusRequest $request, $program_id): JsonResponse
    {
        $this->service->update($request->only('is_disabled'), $program_id);

        return response()->json([ 'message' => 'Program\'s Budget Status Updated Successfully' ]);
    }

}
