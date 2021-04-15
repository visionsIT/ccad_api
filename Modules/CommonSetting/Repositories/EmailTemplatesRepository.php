<?php namespace Modules\CommonSetting\Repositories;

use Carbon\Carbon;
use App\Repositories\Repository;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Nomination\Models\ValueSet;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersGroupList;
use Modules\User\Models\PageVisits;
use Modules\Account\Models\Account;
use Modules\User\Models\UserCampaignsBudget;
use DateTime;
use DB; 
use Modules\CommonSetting\Models\PointRateSettings;
use Modules\Nomination\Models\UserNomination;
use Modules\Reward\Models\ProductOrder;
use Modules\CommonSetting\Models\EmailTemplate;

class EmailTemplatesRepository extends Repository
{

    protected $modeler = CampaignSettings::class;

    public function templateDataExists($templateData){

        $existingTemplateData = EmailTemplate::find((int)$templateData['template_id']);

        if($existingTemplateData){

            $existingTemplateData->template_type_id = $templateData['template_type_id'];
            $existingTemplateData->subject = $templateData['template_subject'];
            $existingTemplateData->content = $templateData['template_content'];
            $existingTemplateData->save();

            return response()->json(['message' => 'Template updated.', 'status'=>true]);
        } else {

            $newTemplateEntry = EmailTemplate::create([
                'template_type_id' => $templateData['template_type_id'],
                'subject' => $templateData['template_subject'],
                'content' => $templateData['template_content'],
            ]);

            if($newTemplateEntry) {
                return response()->json(['message' => 'Template created.', 'status'=>true]);
            } else {
                return response()->json(['message' => 'Something went wrong! Please try again.', 'status'=>false]);
            }
        }
    }

    public function getTemplateData($template_id) {

        $getTemaplate = EmailTemplate::where('id',$template_id)->with("templateType")->first();
        if($getTemaplate) {
            
            $getTemaplate->content  =  str_replace('\n','',$getTemaplate->content);
            
            if(substr($getTemaplate->content,0,1) == '"'){
                $getTemaplate->content =  substr($getTemaplate->content,1,strlen($getTemaplate->content) - 2);
            }
            return response()->json(['message' => 'Template found.', 'status'=>true, 'data' => $getTemaplate]);
        } else {
            return response()->json(['message' => 'Template not found.', 'status'=>false, 'data' => array()]);
        }

    }

    
}
