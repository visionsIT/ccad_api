<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Program\Http\Requests\PointRequest;
use Modules\Program\Http\Services\PointService;
use Modules\Program\Http\Services\ProgramService;
use Modules\Program\Transformers\CurrentBalanceTransformer;
use Modules\Program\Transformers\PointsTransformer;
use Spatie\Fractal\Fractal;

class PointSettingsController extends Controller
{
    private $service, $program_service;

    public function __construct(ProgramService $program_service, PointService $service)
    {
        $this->service         = $service;
        $this->program_service = $program_service;
    }


    /**
     * @param Request $request
     * @param $program_id
     *
     * @return Fractal
     */
    public function index(Request $request, $program_id): Fractal
    {
        $program = $this->program_service->find($program_id);

        $points = $this->service->get(30, $program, $request->query());

        return fractal($points, new PointsTransformer());
    }

    /**
     * @param $program_id
     * @param PointRequest $request
     *
     * @return Fractal
     */
    public function store($program_id, PointRequest $request): Fractal
    {
        $program = $this->program_service->find($program_id);

        $point = $this->service->store($program, $request->all());

        return fractal($point, new PointsTransformer());
    }

    /**
     * @param $program_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentBalance($program_id)
    {
        $program = $this->program_service->find($program_id);

        $current_balance = $this->service->currentBalance($program);

        return fractal($current_balance, new CurrentBalanceTransformer());
    }

}
