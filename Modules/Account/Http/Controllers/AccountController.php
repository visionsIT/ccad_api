<?php namespace Modules\Account\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Account\Http\Requests\AccountPermissionsRequest;
use Modules\Account\Http\Requests\AccountRequest;
use Modules\Account\Http\Services\AccountService;
use Modules\Account\Models\Account;
use Modules\Account\Transformers\AccountTransformer;
use Modules\Nomination\Transformers\BadgesTransformer;
use Spatie\Fractal\Fractal;

class AccountController extends Controller
{
    private $account_service;

    public function __construct(AccountService $account_service)
    {
        $this->account_service = $account_service;
        // $this->middleware('auth:api')->only(['getAuthenticatedAccountData']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $accounts = $this->account_service->get();

        return fractal($accounts, new AccountTransformer);
    }

    /**
     * @param AccountRequest $request
     * @param Carbon $carbon
     *
     * @return Fractal
     */
    public function store(AccountRequest $request, Carbon $carbon): Fractal
    {
        $accounts = $this->account_service->store($request->all() + [ 'login_ip' => $request->ip() ], $carbon);

        return fractal($accounts, new AccountTransformer);
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
        $accounts = $this->account_service->show($id);

        return fractal($accounts, new AccountTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param AccountRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(AccountRequest $request, $id): JsonResponse
    {
        $this->account_service->update($request->all(), $id);

        return response()->json([ 'message' => 'Account Updated Successfully' ]);
    }

    /**
     *
     * Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->account_service->destroy($id);

        return response()->json([ 'message' => 'Account Trashed Successfully' ]);
    }


    /**
     * @param AccountPermissionsRequest $request
     * @param Account $account
     *
     * @return JsonResponse
     */
    public function syncPermissions(AccountPermissionsRequest $request, Account $account): JsonResponse
    {
        $this->account_service->syncPermissions($request->all(), $account);

        return response()->json([ 'message' => 'Permissions Assigned To Account Successfully' ]);
    }

    public function getAuthenticatedAccountData(Request $request)
    {
        return fractal($request->user(), new AccountTransformer);
    }

    public function getAuthenticatedAccountBudges(Request $request)
    {
        $account = $this->account_service->show($request->account_id);

        return $account->badges->map(function ($badge){
            return $badge->types->map(function ($type){
                return (new BadgesTransformer())->transform($type);
            });
        })->filter();


    }

}
