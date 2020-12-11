<?php namespace Modules\Agency\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Agency\Http\Repositories\AgencyRepository;
use Modules\Agency\Http\Requests\AgencyRequest;
use Modules\Agency\Models\Agency;
use Modules\Agency\Transformers\AgencyTransformer;
use Spatie\Fractal\Fractal;

class AgencyController extends Controller
{
    private $repository;

    public function __construct(AgencyRepository $repository)
    {
//        $this->middleware(['permission:browse_agencies'])->only('index');
//        $this->middleware(['permission:add_agencies'])->only('store');
//        $this->middleware(['permission:read_agencies'])->only('show');
//        $this->middleware(['permission:edit_agencies'])->only('update');
//        $this->middleware(['permission:delete_agencies'])->only('destroy');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
//        \Gate::allows('browse_agencies');

        $agencies = $this->repository->get();

        return fractal($agencies, new AgencyTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AgencyRequest $request
     *
     * @return mixed
     */
    public function store(AgencyRequest $request)
    {
        $agency = $this->repository->create($request->all());

        return fractal($agency, new AgencyTransformer());
    }

    /**
     * @param Agency $agency
     *
     * @return Fractal
     */
    public function show(Agency $agency): Fractal
    {
        return fractal($agency, new AgencyTransformer());
    }

    /**
     * @param AgencyRequest $request
     * @param Agency $agency
     *
     * @return JsonResponse
     */
    public function update(AgencyRequest $request, Agency $agency): JsonResponse
    {
        $agency->update($request->all());

        return response()->json([ 'message' => 'Data has been successfully updated ' ]);
    }

    /**
     * @param Agency $agency
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Agency $agency): JsonResponse
    {
        $agency->delete();

        return response()->json([ 'message' => 'Data has been successfully deleted' ]);
    }
}
