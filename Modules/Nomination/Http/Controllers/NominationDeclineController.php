<?php namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\NominationDeclineRequest;
use Modules\Nomination\Http\Services\NominationDeclineService;
use Modules\Nomination\Http\Services\NominationService;
use Modules\Nomination\Transformers\NominationDeclineTransformer;
use Spatie\Fractal\Fractal;

class NominationDeclineController extends Controller
{
    private $nomination_service, $nomination_decline_service;

    public function __construct(NominationService $nomination_service, NominationDeclineService $nomination_decline_service)
    {
        $this->nomination_service         = $nomination_service;
        $this->nomination_decline_service = $nomination_decline_service;
        $this->middleware('auth:api');
    }


    /**
     * @param $nomination_id
     *
     * @return Fractal
     */
    public function index($nomination_id): Fractal
    {
        $NominationDeclines = $this->nomination_service->find($nomination_id)->decline;

        return fractal($NominationDeclines, new NominationDeclineTransformer());
    }

    /**
     * @param $nomination_id
     * @param NominationDeclineRequest $request
     *
     * @return Fractal
     */
    public function store($nomination_id, NominationDeclineRequest $request): Fractal
    {
        $Nomination = $this->nomination_service->find($nomination_id);

        $NominationDecline = $this->nomination_decline_service->store($Nomination, $request->all());

        return fractal($NominationDecline, new NominationDeclineTransformer());
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
        $NominationDecline = $this->nomination_decline_service->show($id);

        return fractal($NominationDecline, new NominationDeclineTransformer());
    }

    /**
     * @param NominationDeclineRequest $request
     * @param $nomination_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(NominationDeclineRequest $request, $nomination_id, $id): JsonResponse
    {
        $this->nomination_decline_service->update($request->all(), $id);
        return response()->json([ 'message' => 'Nomination\'s Decline Updated Successfully' ]);
    }


    /**
     * @param $nomination_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($nomination_id, $id): JsonResponse
    {
        $this->nomination_decline_service->destroy($id);

        return response()->json([ 'message' => 'Nomination\'s Decline Trashed Successfully' ]);
    }

}
