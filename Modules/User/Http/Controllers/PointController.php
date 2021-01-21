<?php namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Http\Requests\PointRequest;
use Modules\User\Http\Services\PointService;
use Modules\Program\Http\Services\ProgramService;
use Modules\Program\Transformers\PointsTransformer as TransformersPointsTransformer;
use Modules\User\Transformers\CurrentBalanceTransformer;
use Modules\User\Http\Services\UserService;
use Modules\User\Http\Services\UserNominationService;
use Spatie\Fractal\Fractal;
use Modules\User\Transformers\PointsTransformer;
use Helper;

class PointController extends Controller
{
    private $service, $user_service, $user_nomination_service;

    /**
     * PointController constructor.
     *
     * @param UserService $user_service
     * @param PointService $service
     */
    public function __construct(UserService $user_service, PointService $service, UserNominationService $user_nomination_service)
    {
        $this->service      = $service;
        $this->user_service = $user_service;
        $this->user_nomination_service = $user_nomination_service;
        $this->middleware('auth:api');
    }


    /**
     * @param Request $request
     * @param $program_id
     * @param $user_id
     *
     * @return Fractal
     */
    public function index(Request $request, $program_id, $user_id): Fractal
    {
        $user   = $this->user_service->find($user_id);
        $points = $this->service->get(30, $user, $request->query());

        return fractal($points, new PointsTransformer());
    }

    /**
     * @param $program_id
     * @param $user_id
     * @param PointRequest $request
     * @param ProgramService $program_service
     *
     * @return Fractal
     */
    public function store($program_id, $user_id, PointRequest $request, ProgramService $program_service): Fractal
    {
        $user    = $this->user_service->find($user_id);
        $program = $program_service->find($program_id);
        $point   = $this->service->store($user, $program, $request->all());

        return fractal($point, new PointsTransformer());
    }

    /**
     * @param $user_id
     * @param PointRequest $request
     *
     * @return Fractal
     */
    public function addPointsToSpecificUser($user_id , PointRequest $request): Fractal
    {
        $user    = $this->user_service->find($user_id);

        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $destinationPath = public_path('uploaded/instant_reward/');
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/instant_reward/';
        $attachmentUrl = '';
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $rules = [
                'attachment' => 'file||mimes:doc,docx,csv,xlsx,xls,txt,pdf,png,jpg,jpeg',
            ];
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()){
                echo "You try to upload invalid file, pleae try doc,docx,csv,xlsx,xls,txt,pdf,png,jpg,jpeg";
                exit();
            } else {
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $notActiveUrl = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $file->move($destinationPath, $notActiveUrl);
                $attachmentUrl = ($notActiveUrl!='')?$imgUrl.$notActiveUrl:'';
            }
        }
        $point   = $this->service->addPointsToSpecificUser($user, $request->all(), $attachmentUrl);

        return fractal($point, new PointsTransformer());
    }

    /**
     * @param $program_id
     * @param $user_id
     *
     * @return Fractal
     */
    public function currentBalance($program_id, $user_id): Fractal
    {

        try{
            $user_id = Helper::customDecrypt($user_id);
            $user            = $this->user_service->find($user_id);

            $user_balance    = $this->user_service->userProfileBalance($user_id);
            $userNominations = $this->user_nomination_service->find($user_id);
            $nominations_count = count($userNominations->toArray());

            $current_balance = $this->service->currentBalance($user);

            $tranformData = json_encode(array('current_bal' => $current_balance, 'nominations' => $nominations_count, 'balance' => $user_balance));

            return fractal($tranformData, new CurrentBalanceTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check user_id and try again.', 'errors' => $th->getMessage()], 402);
        }

    }

    public function filterPointsHistory(Request $request): Fractal {
        $data = $this->service->filterPoints($request->all());
        return fractal($data, new PointsTransformer());
        // try {
        //     $data = $this->service->filterPoints($request->all());
        //     return $data;
        // } catch (\Throwable $th) {
        //     return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()]);
        // }
    }

}
