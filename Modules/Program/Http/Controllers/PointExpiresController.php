<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Program\Http\Requests\PointExpiriesRequest;
use Modules\Program\Http\Services\PointExpiriesService;
use Modules\Program\Http\Services\ProgramService;
use Modules\Program\Transformers\PointsExpiryTransformer;
use Spatie\Fractal\Fractal;

class PointExpiresController extends Controller
{
    private $service, $program_service;

    public function __construct(ProgramService $program_service, PointExpiriesService $service)
    {
        $this->service         = $service;
        $this->program_service = $program_service;
		$this->middleware('auth:api');
    }

    /**
     * @param $program_id
     *
     * @return Fractal
     */
    public function index($program_id): Fractal
    {
        $points_expiry = $this->program_service->find($program_id)->points_expiry;

        return fractal($points_expiry, new PointsExpiryTransformer());
    }

    /**
     * @param PointExpiriesRequest $request
     * @param $program_id
     *
     * @return Fractal
     */
    public function handleExpiration(PointExpiriesRequest $request, $program_id): Fractal
    {
        $program = $this->program_service->find($program_id);

        $points_expiry = $this->service->handleExpiration($program, $request->all());

        return fractal($points_expiry, new PointsExpiryTransformer());
    }

}
