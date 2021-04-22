<?php

namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Nomination\Models\NominationType;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\NominationTypeRequest;
use Modules\Nomination\Transformers\BadgesTransformer;
use Modules\Nomination\Transformers\UserBadgesTransformer;
use Modules\Nomination\Transformers\NominationTypeTransformer;
use Modules\Nomination\Repositories\NominationTypeRepository;
use Helper;


class NominationTypeController extends Controller
{
    private $repository;

    public function __construct(NominationTypeRepository $repository)
    {
        $this->repository = $repository;
		$this->middleware('auth:api');
    }

    /**
     * @return Fractal
     */
    public function index(): Fractal
    {
        $nomination_type = $this->repository->get();
        return fractal($nomination_type, new NominationTypeTransformer);
    }

    /**
     * @param $value_set_id
     * @return Fractal
     */
    public function getNominationTypeBy($value_set_id): Fractal
    {
        $NTypes = $this->repository->getNominationTypeBy($value_set_id);
        return fractal($NTypes, new NominationTypeTransformer);
    }

   /**
     * @param $value_set_id
     * @return Fractal
     */
    public function NominationBadges($account_id): Fractal
    {
        $Badges = NominationType::select('nomination_types.id','nomination_types.name','nomination_types.active_url','nomination_types.not_active_url','nomination_types.status','account_id')
        ->leftJoin('account_badges', function($join) use ($account_id){
          $join->on('nomination_types.id', '=', 'account_badges.nomination_type_id')->where('account_badges.account_id',$account_id);
        })
        ->where('value_set',1)
        ->distinct('nomination_types.id')
        ->get();
        return fractal($Badges, new UserBadgesTransformer);
    }



    /**
     * @param $account_id
     * @return Fractal
     */
    public function myorders($account_id): Fractal
    {
        $nomination_type = $this->repository->UserOrders($account_id);
        return fractal($nomination_type, new NominationTypeTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param NominationTypeRequest $request
     * @return Fractal
     */
    public function store(NominationTypeRequest $request)
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $newname = '';
        $destinationPath = public_path('img/');
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/img/';
        if ($request->hasFile('active_url')) {
            $file = $request->file('active_url');
            $request->validate([
                'attachments' => 'file||mimes:jpeg,png',
            ]);
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $newname = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $file->move($destinationPath, $newname);
        }

        $notActiveUrl = '';
        if ($request->hasFile('not_active_url')) {
            $file = $request->file('not_active_url');
            $request->validate([
                'attachments' => 'file||mimes:jpeg,png',
            ]);
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $notActiveUrl = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $file->move($destinationPath, $notActiveUrl);
        }

        $check_data = NominationType::where(['name'=>$request->name,'value_set'=>$request->value_set])->first();

        if(!empty($check_data)){
            return response()->json(['message' => 'The name has already been taken in this campaign.','status'=>'error']);
        }else{

            $data = [
                'value_set' => $request->value_set,
                'name' => $request->name,
                'description' => $request->description,
                'points' => $request->points,
                'not_active_url' => ($notActiveUrl!='')?$imgUrl.$notActiveUrl:'',
                'active_url' => ($newname!='')?$imgUrl.$newname:'',
                'logo' => ($newname!='')?$imgUrl.$newname:''
            ];

            $nomination_types = $this->repository->create($data);
            return fractal($nomination_types, new NominationTypeTransformer);
        }
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
        $Category = $this->repository->find($id);

        return fractal($Category, new NominationTypeTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param NominationTypeRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(NominationTypeRequest $request, $id): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'value_set' => 'required|exists:value_sets,id',
            'name' => 'required',
            'points' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.'], 422);
        }else{
            $this->repository->update($request->all(), $id);
            return response()->json(['message' => 'Category Updated Successfully']);
            
        }
    }

    /**
     *
     *  Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json(['message' => 'Category Trashed Successfully']);
    }


    /**
     * @param $value_set_id
     * @return mixed
     */
    public function value_set_types($value_set_id)
    {
        return NominationType::where('value_set', $value_set_id)->get(); //todo remove
    }

    public function updateBadges(Request $request)
    {
        $request->validate([
            'nomination_type_id' => 'required|exists:nomination_types,id',
            'times' => 'required|integer',
            'active_url' => 'required|url',
            'not_active_url' => 'required|url',
            'points' => 'required|integer'
        ]);

        $type = $this->repository->find($request->nomination_type_id);

        $type->update([
            'times' => $request->times,
            'active_url' => $request->active_url,
            'not_active_url' => $request->not_active_url,
            'points' => $request->points,
        ]);

        return fractal($type, new BadgesTransformer);

    }

    public function updateTypeData(Request $request, $id)
    {
        try{
            $id =  Helper::customDecrypt($id);
            $rules = [
                'value_set' => 'required|exists:value_sets,id',
                'name' => 'required',
                'points' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

           
            $check_data = NominationType::where(['name'=>$request->name,'value_set'=>$request->value_set])->where('id','!=',$id)->first();
            if(!empty($check_data)){
                return response()->json(['message' => 'The name has already been taken in this campaign.','status'=>'error']);
            }

            $data = [
                'value_set' => $request->value_set,
                'name' => $request->name,
                'points' => $request->points,
                'description' => $request->description,
            ];

            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
            $destinationPath = public_path('img/');
            $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/img/';
            if ($request->hasFile('active_url')) {
                $file = $request->file('active_url');
                $request->validate([
                    'attachments' => 'file||mimes:jpeg,png',
                ]);
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $newname = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $file->move($destinationPath, $newname);

                $data['active_url'] = $imgUrl.$newname;
                $data['logo'] = $imgUrl.$newname;
            }

            if ($request->hasFile('not_active_url')) {
                $file = $request->file('not_active_url');
                $request->validate([
                    'attachments' => 'file||mimes:jpeg,png',
                ]);
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $notActiveUrl = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $file->move($destinationPath, $notActiveUrl);

                $data['not_active_url'] = $imgUrl.$notActiveUrl;
            }

            $this->repository->update($data, $id);
            $nomination_types = $this->repository->find($id);
            return fractal($nomination_types, new NominationTypeTransformer);
        }
        catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function updateStatus(Request $request) {
        try {
            $request['id'] = Helper::customDecrypt($request->id);
            $rules = [
                'id' => 'required|integer|exists:nomination_types,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $nominationType = $this->repository->find($request->id);
            $nominationType->status = $request->status;
            $nominationType->save();

            return response()->json(['message' => 'Status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }
}
