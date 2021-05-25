<?php namespace Modules\Account\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Modules\Agency\Models\ClientsAdmin;
use Modules\Nomination\Models\NominationType;
use Modules\User\Models\ProgramUsers;
use Spatie\Permission\Traits\HasRoles;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\User\Models\TeamsAccountsLink;
use Modules\User\Models\Teams;
use Modules\User\Models\Departments;
use Modules\User\Transformers\TeamsTransformer;
use Modules\User\Models\UsersGroupList;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\CampaignTypes;

use DB;


class Account extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * @var array
     */
    protected $dates = [ 'last_login' ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'last_login', 'login_ip', 'type', 'contact_number', 'email_verified_at','def_dept_id','login_attempts'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return HasOne
     */
    public function client_admins()
    {
        return $this->hasOne(ClientsAdmin::class); //todo WTF
    }

    /**
     * @param $password
     */
    public function setPasswordAttribute($password): void
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function user()
    {
        return $this->hasOne(ProgramUsers::class);
    }

    public function defaultDepartment()
    {
        return $this->hasOne(Departments::class,'id','def_dept_id');
    }

    /**
     * @return HasMany
     */
    public function badges()
    {
        return $this->hasMany(AccountBadges::class);
    }

    /**
     * @return HasMany
     */
    public function teams()
    {
        return $this->hasMany(TeamsAccountsLink::class,'account_id','id');
    }

    /**
     * @return HasMany
     */
    public function getTeams()
    {
        $response = [];
        $account_link_team = $this->teams;
        $collection = collect($account_link_team)->map(function ($model) {
            return $model->team_id;
        });
        if(count($collection) > 0){
            $teams =  Teams::whereIN('id',$collection)->get();
            $response = fractal($teams, new TeamsTransformer())->includeCharacters()->toArray();
            $response = $response['data'];
        }

        return $response;
    }
    public function department()
    {
        return $this->hasOne(Departments::class,'id','def_dept_id');
    }

    public function groupRoles(){
        //return $this->hasMany(UsersGroupList::class);
        return $this->hasMany(UsersGroupList::class,'account_id','id');
    }

    public function campaign($id){

        $all_campaign = [];
        $check_user = ProgramUsers::where(['account_id'=>$id,'is_active'=>1])->first();
        if(!empty($check_user)){

            $roles = DB::table('users_group_list')->where('account_id', $id)->join('user_roles', 'user_roles.id', '=', 'users_group_list.user_role_id')->get();
           /// echo max(array_column($roles, 'user_role_id'));die;
            $max_role_id = 0;
            foreach($roles as $obj)
            {
                if($obj->user_role_id > $max_role_id)
                {
                    $max_role_id = $obj->user_role_id;
                }
            }

            $campaign_type = ValueSet::select('id','campaign_slug')->where('status', '1')->get();
            $finalArray = [];
            foreach($campaign_type as $campaign){

                $camSett = DB::table('campaign_settings')->where(['campaign_id'=>$campaign->id])->first();

                if(!empty($camSett)){
                    if($camSett->s_eligible_user_option == '0'){
                        #all_users
                        $finalArray[$campaign->campaign_slug] = '1';
                    }else if($camSett->s_eligible_user_option == '1'){
                        #levelwise
                        if($max_role_id == 3){
                            if($camSett->s_level_option_selected == '1' || $camSett->s_level_option_selected == '2'){
                                $finalArray[$campaign->campaign_slug] = '1';
                            }else{
                                $finalArray[$campaign->campaign_slug] = '0';
                            }
                        }else if($max_role_id == 2){//L1
                            if($camSett->s_level_option_selected == '0' || $camSett->s_level_option_selected == '2'){
                                $finalArray[$campaign->campaign_slug] = '1';
                            }else{
                                $finalArray[$campaign->campaign_slug] = '0';
                            }
                        }else{
                            $finalArray[$campaign->campaign_slug] = '0';
                        }

                    }else if($camSett->s_eligible_user_option == '2'){
                        #multiplt_user_groups
                        $usereligibility = '0';
                        if($camSett->s_user_ids != null){

                            $get_user_ids = $camSett->s_user_ids;
                            $userIds = explode(',',$get_user_ids);
                            if (in_array($check_user->id, $userIds)) {
                                $usereligibility = '1';
                            }

                        }
                        if($camSett->s_group_ids != null){
                            #check_group_ids
                            $get_group_ids = $camSett->s_group_ids;
                            $groupIds = explode(',',$get_group_ids);

                            foreach($groupIds as $grpID){
                                $check_grps = DB::table('users_group_list')->distinct('user_group_id')->select('user_group_id')->where(['account_id'=>$id,'user_role_id'=>$max_role_id,'user_group_id'=>$grpID])->get()->toArray();

                                if(!empty($check_grps)){
                                    $usereligibility = '1';
                                }
                            }

                        }
                        $finalArray[$campaign->campaign_slug] = $usereligibility;

                    }else{
                        $finalArray[$campaign->campaign_slug] = '0';
                    }

                }#endif_camsett


            }#foreach_ends
            $all_campaign = $finalArray;

        }




        return $all_campaign;


    }/**fn ends****/
}
