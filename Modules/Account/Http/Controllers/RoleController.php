<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Account\Http\Services\RoleService;
use Modules\Account\Models\Account;
use Modules\Account\Transformers\PermissionTransformer;
use Modules\User\Transformers\UserTransformer;
use \Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controller;
use Modules\Account\Http\Requests\RoleRequest;
use Modules\Account\Http\Requests\PagePermissionRequest;
use Modules\Account\Transformers\RoleTransformer;
use Modules\Account\Repositories\RoleRepository;

class RoleController extends Controller
{
    private $service;
    private $repository;

    public function __construct(RoleRepository $repository, RoleService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
		$this->middleware('auth:api');
    }

    /**
     * @param $id
     * @return Fractal
     */
    public function index(Request $request, $id): Fractal
    {
        if(isset($request->search) && $request->search !=''){
            $agencies = $this->repository->getRolesByProgram($id, $request->search);
        } else {
            $agencies = $this->repository->getRolesByProgram($id);
        }

        return fractal($agencies, new RoleTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoleRequest $request
     *
     * @return mixed
     */
    public function store(RoleRequest $request)
    {
        $role = $this->repository->create($request->all());

        return fractal($role, new RoleTransformer());
    }

    /**
     * @param Role $role
     *
     * @return Fractal
     */
    public function show(Role $role): Fractal
    {
        return fractal($role, new RoleTransformer());
    }

    /**
     * @param RoleRequest $request
     * @param Role $role
     *
     * @return JsonResponse
     */
    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->all());

        return response()->json([ 'message' => 'Data has been successfully updated ' ]);
    }

    /**
     * @param Role $role
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json([ 'message' => 'Data has been successfully deleted' ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRoleAccounts($id)
    {
        $users = $this->service->getRoleUsers($id);

        return fractal($users , new UserTransformer());

    }

    /**
     * @param Request $request
     * @return Fractal
     */
    public function assignPermissionsToGroup(Request $request)
    {

        $request->validate([
            'permission_names.*'    => 'required|string|exists:permissions,name'
        ]);

        $role = $this->service->assignPermissionsToGroup($request->role_id, $request->permission_names);

        return fractal($role , new RoleTransformer());

    }


    public function getRolePermissions($id)
    {
        $permissions = $this->service->getRolePermissions($id);

        return fractal($permissions , new PermissionTransformer());

    }

    /**
     * change group permissions to access frondend pages
     *
     * @param PagePermissionRequest $request
     *
     * @return mixed
     */
    public function changePermission(PagePermissionRequest $request)
    {
        $role = $this->repository->find($request->group_role_id);
        if($request->change_permission_of == 'nomination_approval_access'){
            $role->nomination_approval_access = $request->set_permission;
        } elseif ($request->change_permission_of == 'instant_point_access') {
            $role->instant_point_access = $request->set_permission;
        } elseif ($request->change_permission_of == 'project_compaign_access') {
            $role->project_compaign_access = $request->set_permission;
        } elseif ($request->change_permission_of == 'general_permission') {
            $role->general_permission = $request->set_permission;
        } elseif ($request->change_permission_of == 'rewards_module_permission') {
            $role->rewards_module_permission = $request->set_permission;
        } elseif ($request->change_permission_of == 'birthday_campaign_permission') {
            $role->birthday_campaign_permission = $request->set_permission;
        }
        $role->save();
        return fractal($role, new RoleTransformer());
    }

    public function updateRoleStatus(Request $request){
        try {
            $rules = [
                'id' => 'required|integer|exists:roles,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $programUser = $this->repository->find($request->id);
            $programUser->status = $request->status;
            $programUser->save();

            return response()->json(['message' => 'Status has been changed successfully.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function updateRoleInfo(Request $request, $id): JsonResponse
    {
        try {
            $rules = [
                'name'    => 'required|string|unique:roles,name,'.$id,
                'program_id' => 'required|exists:programs,id',
                'group_level_id' => 'required|exists:group_levels,id',
                'group_level_parent_id' => 'sometimes|integer'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $roleInfo = $this->repository->find($id);
            $roleInfo->name = $request->name;
            $roleInfo->program_id = $request->program_id;
            $roleInfo->group_level_id = $request->group_level_id;
            $roleInfo->group_level_parent_id = $request->group_level_parent_id;
            $roleInfo->save();

            return response()->json(['message' => 'Data has been updated successfully.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function updateCampaignMessage(Request $request): JsonResponse
    {
        try {
            $rules = [
                'id'    => 'required|exists:roles,id',
                'birthday_message' => 'required',
                'birthday_points' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $roleInfo = $this->repository->find($request->id);
            $roleInfo->birthday_message = $request->birthday_message;
            $roleInfo->birthday_points = $request->birthday_points;
            $roleInfo->save();

            return response()->json(['message' => 'Data has been updated successfully.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

}
