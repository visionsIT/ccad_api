<?php namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\SetApprovalRequest;
use Modules\Nomination\Http\Services\NominationService;
use Modules\Nomination\Http\Services\SetApprovalService;
use Modules\Nomination\Transformers\SetApprovalTransformer;
use Spatie\Fractal\Fractal;

class SetApprovalController extends Controller
{
    private $nomination_service, $set_approval_service;

    public function __construct(NominationService $nomination_service, SetApprovalService $set_approval_service)
    {
        $this->nomination_service = $nomination_service;
        $this->set_approval_service = $set_approval_service;
        $this->middleware('auth:api');
    }


    /**
     * @param $nomination_id
     *
     * @return Fractal
     */
    public function index($nomination_id): Fractal
    {
        $SetApprovals = $this->nomination_service->find($nomination_id)->set_approval;

        return fractal($SetApprovals, new SetApprovalTransformer());
    }

    /**
     * @param $nomination_id
     * @param SetApprovalRequest $request
     *
     * @return Fractal
     */
    public function store($nomination_id, SetApprovalRequest $request): Fractal
    {
        $Nomination = $this->nomination_service->find($nomination_id);
        $SetApproval = $this->set_approval_service->store($Nomination, $request->all());

        return fractal($SetApproval, new SetApprovalTransformer());
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
        $SetApproval = $this->set_approval_service->show($id);

        return fractal($SetApproval, new SetApprovalTransformer());
    }

    /**
     * @param SetApprovalRequest $request
     * @param $nomination_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(SetApprovalRequest $request, $nomination_id, $id): JsonResponse
    {
        $this->set_approval_service->update($request->all(), $id);
        return response()->json(['message' => 'Nomination\'s Approval  Updated Successfully']);
    }


    /**
     * @param $nomination_id
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($nomination_id, $id): JsonResponse
    {
        $this->set_approval_service->destroy($id);

        return response()->json(['message' => 'Nomination\'s Approval Trashed Successfully']);
    }

}



