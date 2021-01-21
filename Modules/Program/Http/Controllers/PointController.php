<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Program\Http\Requests\PointRequest;
use Modules\Program\Http\Services\PointService;
use Modules\Program\Http\Services\ProgramService;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersPoint;
use Modules\Program\Transformers\CurrentBalanceTransformer;
use Modules\Program\Transformers\PointsTransformer;
use Spatie\Fractal\Fractal;
use DB;

class PointController extends Controller
{
    private $service, $program_service;

    /**
     * PointController constructor.
     *
     * @param ProgramService $program_service
     * @param PointService $service
     */
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
        $point   = $this->service->store($program, $request->all());

        return fractal($point, new PointsTransformer());
    }

    /**
     * @param $program_id
     *
     * @return Fractal
     */
    public function currentBalance($program_id): Fractal
    {
        $program         = $this->program_service->find($program_id);
        $current_balance = $this->service->currentBalance($program);

        return fractal($current_balance, new CurrentBalanceTransformer());
    }


    public function totalBalanceByProgramid($program_id) {


        $current_budget_bal = UsersPoint::select('balance')->where('user_id',$program_id)->latest()->first();

        if($current_budget_bal){
            $budget_bal = $current_budget_bal->balance;
        }else{
            $budget_bal = 0;
        }

        try {
            return response()->json(['data' => array("current_balance" => $budget_bal , "user_balance" => 0, "user_nominations" => 0 )], 200);

        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

    public function pointsHistoryListing() {
        try {
            $pointsHistory = $this->service->pointsHistory();
            return $pointsHistory;
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()]);
        }
    }

    public function birthdayPoints() {
        try {
            //code...
            $todayDate = date("m-d");
            $users = ProgramUsers::where('date_of_birth', 'LIKE', "%{$todayDate}%")->get();
            if ($users) {
                $roleCheckArr = [];
                for($i = 0; $i < count($users); $i++) {
                    $roleData = DB::table('model_has_roles')->select('roles.*')->where(['model_id' => $users[$i]->account_id])->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->get()->first();

                    array_push($roleCheckArr, $roleData);

                    if ($roleData) {
                        if ($roleData->birthday_campaign_permission == 1 && $roleData->birthday_points > 0){

                            $pointsBal = 0;

                            $pointsData = UsersPoint::where('user_id', $users[$i]->id)->orderBy('id', 'desc')->first();

                            if($pointsData) {
                                $pointsBal = (int)$pointsData->balance + (int)$roleData->birthday_points;
                            } else {
                                $pointsBal = $roleData->birthday_points;
                            }
                            UsersPoint::create([
                                'user_id'    => $users[$i]->id,
                                'value'    => $roleData->birthday_points,
                                'transaction_type_id'    => 4, // birthday points type
                                'balance'    => $pointsBal,
                                'created_by_id' => $users[$i]->account_id
                                ]);
                        }
                    }
                }
            }
            return response()->json(['users' => $users, 'Roles' => $roleCheckArr]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['Error' => $th->getMessage()]);
        }
    }

}
