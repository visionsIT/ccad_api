<?php

namespace Modules\Account\Http\Controllers;

use Modules\Account\Http\Services\PermissionService;
use Modules\Account\Models\Account;
use Modules\Account\Models\Permission;
use Modules\User\Models\ProgramUsers;
use Modules\User\Transformers\UserTransformer;
use Spatie\Fractal\Fractal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Account\Transformers\PermissionTransformer;
use Modules\Account\Repositories\PermissionRepository;
use DB;

class PermissionController extends Controller
{
    private $repository;
    private $service;

    public function __construct(PermissionRepository $repository, PermissionService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
        $this->middleware('auth:api', ['except' => ['getPermissionForClaimAwards']]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $permissions = $this->repository->get();

        return $permissions->map(function ($permission) {
            return (new PermissionTransformer())->transform($permission);
        })->groupBy(['table_name']);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        return response()->json($request);
    }


    /**
     * @param Request $request
     *
     * @return Fractal
     */
    public function search(Request $request): Fractal
    {
        $users = $this->repository->search($request->all());

        return fractal($users, new PermissionTransformer());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPermissionAccounts($id)
    {
        $users = $this->service->getPermissionUsers($id);

        return fractal($users , new UserTransformer());

    }

    public function getPermissionForClaimAwards() {
        $claimAwardPermission = $this->service->getClaimAwardPermission();
        return response()->json(['data' => $claimAwardPermission]);
    }

    public function updateClaimAwardPermisssion(Request $request) {
        $updateClaimAward = $this->service->updateClaimAwardDisplayPermission($request->all());
        return response()->json(['data' => $updateClaimAward]);
    }

    public function changeBirthdayPermissionsGlobal(Request $request) {
        try {
            //code...
            $rules = [
                'permission_param' => 'required',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $permissionInfo = $this->service->checkBirthdayPermission($request->all());

            $permissionInfo->status = $request->status;
            $permissionInfo->save();

            return response()->json(['message' => 'Data has been updated successfully.'], 200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function ecardPermission(Request $request) {
        try {
            $input =  $request->all();

            DB::table('permissions')
              ->where('name', 'e_cards')
              ->update(['status' => $input['display']]);

            return response()->json(['status' => true, 'message' => 'E-Cards display setting updated.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function changePermissionStatus(Request $request) {
        try {
            $rules = [
                'id' => 'required|integer',
                'status' => 'required|integer',
            ];
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            DB::table('permissions')
              ->where('id', $request->id)
              ->update(['status' => $request->status]);

            return response()->json(['status' => true, 'message' => 'Setting has been changed successfully.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function fetchReportsAccess() {
        try {

            $response = response()->json(['status' => false, 'message' => 'no data found', 'data' => []]);

            $reports = DB::table('permissions')
            ->where('name', 'reports_emp_access')
            ->orWhere('name', 'reports_l1_access')
            ->orWhere('name', 'reports_l2_access')
            ->get();

            if ($reports && count($$reports->toArray()) > 0) {
                $response = response()->json(['status' => true, 'message' => 'settings data', 'data' => $reports->toArray()]);
            }

            return $response;
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong! Please try again.', 'errors' => $th->getMessage(), 'line' => $th->getLine()], 402);
        }
    }

    public function reportsAccessUpdates(Request $request) {
        try {
            $input =  $request->all();
            $rules = [
                'name' => 'required',
                'status' => 'required|integer',
            ];
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            DB::table('permissions')
            ->where('name', $request->name)
            ->update(['status' => $request->status]);

            return response()->json(['status' => true, 'message' => 'Setting has been updated successfully.']);

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong! Please try again.', 'errors' => $th->getMessage(), 'line' => $th->getLine()], 402);
        }
    }
}
