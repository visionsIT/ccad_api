<?php namespace Modules\CommonSetting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductDenomination;
use Validator;
use DB;
use Modules\CommonSetting\Repositories\EmailTemplatesRepository;
use Modules\CommonSetting\Http\Services\EmailService;
use Helper;
use Response;
use Carbon\CarbonPeriod;
use DateTime;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\UserNomination;
use Modules\User\Models\UsersGroupList;
use File;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\ProgramUsers;
use Carbon\Carbon;
use Modules\CommonSetting\Models\EmailTemplateType;
use Modules\CommonSetting\Models\EmailTemplate;
use Modules\CommonSetting\Transformers\EmailTemplatesTransformer;


class EmailTemplatesController extends Controller
{
    public function __construct(EmailTemplatesRepository $email_repository,EmailService $email_service)
    {
       // $this->middleware('auth:api');
        $this->email_service = $email_service;
        $this->email_repository = $email_repository;
    }
    

    public function emailTemplateTypes(Request $request){
        try{

            $data = EmailTemplateType::where('status','1')->get();
            return response()->json(['message' => 'success', 'status'=>'success', 'data' =>$data]);

        }
        catch(\Throwable $th){

            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);

        }
    }





    public function emailTemplates(Request $request){
        try{
            $data = EmailTemplate::where('status','1')->get();
            return fractal($data, new EmailTemplatesTransformer());
            //return response()->json(['message' => 'success', 'status'=>'success', 'data' =>$data]);
        }
        catch(\Throwable $th){

            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);

        }
        
    }


    public function emailTemplateStatusChange(Request $request){

        $rules = [
            'template_id' => 'required|integer|exists:email_templates,id',
            'status' => 'required|in:0,1',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        try{
            
            $update_data = ['status'=>$request->status];
            $update =  EmailTemplate::where('id',$request->template_id)->update($update_data);
            return response()->json(['message' => 'Status changed successfully', 'status' => 'success','data' => array()], 200);
        }
        catch(\Throwable $th){

            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);

        }
        
    }

    public function saveDynamicEmailContent(Request $request) {
        try {

            $inputs = $request->all();

            $rules = [
                'template_type_id' => 'required|integer|exists:email_template_types,id',
                'template_subject' => 'required',
                'template_content'      => 'required'
            ];

            $validator = \Validator::make($inputs, $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            return $this->email_service->saveTemplateData($inputs);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    public function getEmailTemplateByID($template_id = null) {
        try {

            if(isset($template_id) && $template_id !== null) {
                return $this->email_service->getEmailTemplateByID($template_id);
            } else {
                return response()->json(['message' => 'Something went wrong while fetching template! Please try again.', 'status'=>false, 'errors' => 'template_id should not be null and must carry a value']);
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

}


