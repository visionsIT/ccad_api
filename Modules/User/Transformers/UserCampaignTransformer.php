<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersPoint;
use Modules\User\Models\UserCampaignsBudget;
use Modules\Reward\Models\ProductOrder;
use Helper;
class UserCampaignTransformer extends TransformerAbstract
{
    /**
     * @param ProgramUsers $User
     *
     * @return array
     */
    public function transform(UserCampaignsBudget $data): array
    {

        $user_profile_img = '';
        if(isset($data->user->profile_image) && $data->user->profile_image != '' && $data->user->profile_image != null && $data->user->profile_image != 'null'){
            $profile_img = '/'.$data->user->image_path.$data->user->profile_image;
            $user_profile_img = url($profile_img);
        }
        
        return [
            'id' => Helper::customCrypt($data->id),
            'campaign_id' => Helper::customCrypt($data->campaign_id),
            'budget' => $data->budget,
            'description' => $data->description,
            'program_user_id' => Helper::customCrypt($data->program_user_id),
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
            'program_id' => $data->user->program_id,
            'account_id' => Helper::customCrypt($data->user->account_id),
            'emp_number' => $data->user->emp_number,
            'vp_emp_number' => $data->user->vp_emp_number,
            'first_name' => $data->user->first_name,
            'last_name' => $data->user->last_name,
            'email' => $data->user->email,
            'username' => $data->user->username,
            'company' => $data->user->company,
            'job_title' => $data->user->job_title,
            'address_1' => $data->user->address_1,
            'address_2' => $data->user->address_2,
            'town' => $data->user->town,
            'postcode' => $data->user->postcode,
            'country' => $data->user->country,
            'point_balance' => $data->user->point_balance,
            'telephone' => $data->user->telephone,
            'mobile' => $data->user->mobile,
            'date_of_birth' => $data->user->date_of_birth,
            'communication_preference' => $data->user->communication_preference,
            'language' => $data->user->language,
            'is_active' => $data->user->is_active,
            'ripple_budget' => $data->user->ripple_budget,
            'profile_image' => $user_profile_img,
        ];
    }
}
