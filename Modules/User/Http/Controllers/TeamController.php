<?php namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use \Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\User\Models\Teams;
use Modules\User\Transformers\TeamsTransformer;
use Modules\Account\Models\Account;
use Modules\User\Http\Requests\TeamRequest;
use Modules\User\Http\Services\UserService;
use Validator;
use Helper;

class TeamController extends Controller
{
    private $teams;

    public function __construct(Teams $teams, UserService $service)
    {
        $this->service = $service;
        $this->teams = $teams;
		$this->middleware('auth:api', ['except' => ['newFeedback']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $teams = $this->teams->get();

        return fractal($teams, new TeamsTransformer());
    }

    /**
     * Display a listing of the current logged-in employees teams.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function getUserTeams(Account $account_id): Fractal
    {
        $account_link_team = $account_id->teams;
        $collection = collect($account_link_team)->map(function ($model) {
            return $model->team_id;
        });
        $teams = $this->teams->whereIN('id',$collection)->get();

        return fractal($teams, new TeamsTransformer());
    }

    /**
     * Create Team.
     *
     * @param TeamRequest $request
     *
     * @return mixed
     */
    public function store(TeamRequest $request)
    {
        $team = Teams::create($request->all());

        return fractal($team, new TeamsTransformer());
    }

    /**
     * @param TeamRequest $request
     * @param Team $team
     *
     * @return JsonResponse
     */
    public function update(TeamRequest $request, $id): JsonResponse
    {
        $team = Teams::where('id',$id)->first();
        $message = 'Invalid Id';
        if(!empty($team)){
            $message = 'Data has been successfully updated';
            $team->update($request->all());
        }

        return response()->json([ 'message' => $message ]);
    }

    /**
     * @param TeamRequest $team
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id): JsonResponse
    {
        $team = Teams::where('id',$id)->first();
        $message = 'Invalid Id';
        if(!empty($team)){
            $message = 'Data has been successfully deleted';
            $team->delete();
        }

        return response()->json([ 'message' => $message ]);
    }

    public function newFeedback(Request $request)
    {
        if(isset($request->user_id)){
            $this->middleware('auth:api');
            try{
                $request['user_id'] =  Helper::customDecrypt($request->user_id);

            }catch (\Throwable $th) {
                return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
            }
        }
        $rules = [
            'email'    => 'required|email',
            'feedback' => 'required|string|min:5|max:600',
        ];

        if(isset($request->user_id)){
            $rules['user_id'] = 'required|integer|exists:program_users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()]);
        }

        return $this->service->newUserFeedback($request->all());
    }
}
