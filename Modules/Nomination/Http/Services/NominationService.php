<?php namespace Modules\Nomination\Http\Services;

use Illuminate\Support\Collection;
use Modules\Account\Http\Services\PermissionService;
use Modules\Account\Http\Services\RoleService;
use Modules\Account\Models\Account;
use Modules\Nomination\Models\Nomination;
use Modules\Nomination\Models\UserNomination;
use Modules\Nomination\Repositories\NominationRepository;
use Modules\Account\Repositories\TokensRepository;
use Modules\Account\Http\Repositories\AccountRepository;
use Illuminate\Support\Facades\Mail;
use Modules\Nomination\Repositories\SetApprovalRepository;
use Modules\User\Http\Services\UserService;
use Modules\User\Models\ProgramUsers;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Nomination\Models\ValueSet;
use Modules\Program\Models\UsersEcards;
use DB;

class NominationService
{
    private $repository;
    private $role_service;
    private $user_service;
    private $tokens_repository;
    private $account_repository;
    private $permission_service;

    public function __construct(TokensRepository $tokens_repository,NominationRepository $repository,AccountRepository $account_repository, RoleService $roleService, PermissionService $permissionService, UserService $userService)
    {
        $this->tokens_repository = $tokens_repository;
        $this->account_repository = $account_repository;
        $this->repository = $repository;
        $this->permission_service = $permissionService;
        $this->role_service = $roleService;
        $this->user_service = $userService;
    }


    /**
     * @param $pagination_count
     * @param array $data
     *
     * @return mixedModules\Nomination\Repositories\Nominat
     */
    public function get($pagination_count, $data = [])
    {
        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->paginate($pagination_count);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function store($data)
    {
        return $this->repository->create($data);
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $data
     * @param $id
     */
    public function update($data, $id): void
    {
        $this->repository->update($data, $id);
    }


    /**
     * @param $id
     */
    public function destroy($id): void
    {
        $this->repository->destroy($id);
    }


    /**
     * @param Nomination $nomination
     * @return mixed
     */
    public function getFirstLevelApprovalUsers(Nomination $nomination)
    {
        $approvals = $nomination->set_approval; //todo the relation should be one to one

        $users = collect();

        foreach ($approvals as $approval){
            //todo relation is working but i preferred to get it manually
            if($approval->level_1_approval_type === 'permission')
                $users->push($this->permission_service->getPermissionUsers($approval->level_1_permission));

            $users = $this->role_service->getRoleUsers($approval->level_1_group);

            if (is_array(unserialize($approval->level_1_user))){
               foreach (unserialize($approval->level_1_user) as $value){
                   $users->push($this->user_service->find($value));
               }
            }

        }

        return $users;

    }

    /**
     * @param Nomination $nomination
     * @return mixed
     */
    public function getSecondLevelApprovalUsers(Nomination $nomination)
    {
        $approvals = $nomination->set_approval;

        $users = collect();

        foreach ($approvals as $approval){
            //todo relation is working but i preferred to get it manually
            if($approval->level_2_approval_type === 'permission')
                $users->push($this->permission_service->getPermissionUsers($approval->level_2_permission));

            $users = $this->role_service->getRoleUsers($approval->level_2_group);

            if (is_array(unserialize($approval->level_2_user))){
                foreach (unserialize($approval->level_2_user) as $value){
                    $users->push($this->user_service->find($value));
                }
            }

        }

        return $users;
    }

    /**
     * @param UserNomination $userNomination
     * @return Collection
     */
    public function getApprovalAdmin(UserNomination $userNomination)
    {
        $user = Account::where('id',$userNomination->user)->first();

        // $user_group_name = $userNomination->account->getRoleNames()[0];
        //$user_group_name = $user->getRoleNames()[0];
        $user_group_name = "ADPortVP";
        $accounts = Account::whereHas('roles', function($q)
        {
            $q->where(['name' => 'ADPortVP']);
        })
        ->where(['def_dept_id' => $user->def_dept_id])
        ->get();
        return $accounts->map(function ($account){
            return $account->user;
        })->filter();
        // $users  = collect();

        // $users->push(ProgramUsers::where('first_name', $user_group_name)->first());

        // return $users;
    }

    public function getCampaignUSerNomination($search = NULL,$campaign_id = NULL){

        if($search === NULL) {

            $data = UserNomination::select('user_nominations.*')->where(function($q){
                    $q->where(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '2');
                    })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '2')
                        ->where('user_nominations.level_2_approval', '1');
                    })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '2')
                        ->where('user_nominations.level_2_approval', '2');
                    })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '1');
                    });
                })
                ->join('value_sets', 'user_nominations.campaign_id', '=', 'value_sets.id')
                ->join('campaign_settings', 'user_nominations.campaign_id', '=', 'campaign_settings.campaign_id')
                ->where('value_sets.status','1')
                ->where('campaign_settings.wall_settings','1');

            if($campaign_id === NULL){
                $data = $data->orderByDesc('user_nominations.created_at')->paginate(10);
            }else{
                $data = $data->where('value_sets.id',$campaign_id)->orderByDesc('user_nominations.created_at')->paginate(10);
            } 

            return $data;

        }else{

            $data = UserNomination::select('user_nominations.*')->where(function($q){
                    $q->where(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '2');
                    })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '2')
                        ->where('user_nominations.level_2_approval', '1');
                    })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '2')
                        ->where('user_nominations.level_2_approval', '2');
                    })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '1');
                    });
                })
                ->join('value_sets', 'user_nominations.campaign_id', '=', 'value_sets.id')
                ->join('campaign_settings', 'user_nominations.campaign_id', '=', 'campaign_settings.campaign_id')
                ->where('value_sets.status','1')
                ->where('campaign_settings.wall_settings','1')
                ->where('user_nominations.user',$search);
                

                if($campaign_id === NULL){
                    $data = $data->orderByDesc('user_nominations.created_at')->paginate(10);
                }else{
                    $data = $data->where('value_sets.id',$campaign_id)->orderByDesc('user_nominations.created_at')->paginate(10);
                }

                return $data;

        }

    }

    public function getCampaignEcards($search = NULL){
        if($search === NULL) {

            $data = UsersEcards::select("users_ecards.id as cardid", "users_ecards.ecard_id","users_ecards.campaign_id","users_ecards.image_message","users_ecards.sent_to","users_ecards.sent_by","users_ecards.points","users_ecards.send_type","users_ecards.new_image","users_ecards.image_path","users_ecards.created_at as card_create" )->join('value_sets', 'users_ecards.campaign_id', '=', 'value_sets.id')
                ->join('campaign_settings', 'users_ecards.campaign_id', '=', 'campaign_settings.campaign_id')
                ->where('value_sets.status','1')
                ->where('campaign_settings.wall_settings','1')
                ->orderByDesc('users_ecards.created_at')
                ->paginate(10);

            foreach($data as $key => $value){
                $user_ecard_id = $value->ecard_id;
                $get_nomination = UserNomination::where(function($q){
                    $q->where(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '2');
                    })
                    ->orWhere(function($query){
                            $query->where('user_nominations.level_1_approval', '2')
                            ->where('user_nominations.level_2_approval', '1');
                        })
                    ->orWhere(function($query){
                            $query->where('user_nominations.level_1_approval', '2')
                            ->where('user_nominations.level_2_approval', '2');
                        })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '1');
                    });
                })->where('ecard_id',$user_ecard_id)->get();

                if(!empty($get_nomination)){
                    $data[$key]['user_nominations'] = $get_nomination;
                }

            }
            return $data;


        }else{

            $data = UsersEcards::select("users_ecards.id as cardid", "users_ecards.ecard_id","users_ecards.campaign_id","users_ecards.image_message","users_ecards.sent_to","users_ecards.sent_by","users_ecards.points","users_ecards.send_type","users_ecards.new_image","users_ecards.image_path","users_ecards.created_at as card_create" )->join('value_sets', 'users_ecards.campaign_id', '=', 'value_sets.id')
                ->join('campaign_settings', 'users_ecards.campaign_id', '=', 'campaign_settings.campaign_id')
                ->join('program_users', 'users_ecards.sent_to', '=', 'program_users.id')
                ->where('value_sets.status','1')
                ->where('campaign_settings.wall_settings','1')
                ->where('program_users.first_name', 'LIKE', "%{$search}%")
                ->orWhere('program_users.last_name', 'LIKE', "%{$search}%")
                ->orWhere('users_ecards.campaign_id', '=', $search)
                ->orderByDesc('users_ecards.created_at')
                ->paginate(10);

            foreach($data as $key => $value){
                $user_ecard_id = $value->ecard_id;
                $get_nomination = UserNomination::where(function($q){
                    $q->where(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '2');
                    })
                    ->orWhere(function($query){
                            $query->where('user_nominations.level_1_approval', '2')
                            ->where('user_nominations.level_2_approval', '1');
                        })
                    ->orWhere(function($query){
                            $query->where('user_nominations.level_1_approval', '2')
                            ->where('user_nominations.level_2_approval', '2');
                        })
                    ->orWhere(function($query){
                        $query->where('user_nominations.level_1_approval', '1')
                        ->where('user_nominations.level_2_approval', '1');
                    });
                })->where('ecard_id',$user_ecard_id)->get();

                if(!empty($get_nomination)){
                    $data[$key]['user_nominations'] = $get_nomination;
                }

            }
            return $data;
        }
    }

    /**
     * @param Nomination $nomination
     * @return mixed
     */
    public function getFirstLevelWallUsers(Nomination $nomination, $search = NULL)
    {

        if($search === NULL) {
            return  $nomination->user_nomination()
            ->join('nominations', 'user_nominations.nomination_id', '=', 'nominations.id')
            ->join('value_sets', 'nominations.value_set', '=', 'value_sets.id')
            ->join('campaign_settings', 'nominations.value_set', '=', 'campaign_settings.campaign_id')
            ->where('value_sets.status','1')
            ->where('campaign_settings.wall_settings','1')
            ->where('user_nominations.level_1_approval', 1)->orderByDesc('user_nominations.id')->paginate(10);

            //return $nomination->user_nomination()->where('user_nominations.level_1_approval', 1)->orderByDesc('user_nominations.id')->paginate(10);
        } else {
            return $nomination
                ->user_nomination()
                ->join('program_users', 'user_nominations.user', '=', 'program_users.account_id')
                ->join('nominations', 'user_nominations.nomination_id', '=', 'nominations.id')
                ->join('value_sets', 'nominations.value_set', '=', 'value_sets.id')
                ->join('campaign_settings', 'nominations.value_set', '=', 'campaign_settings.campaign_id')
                ->where('program_users.first_name', 'LIKE', "%{$search}%")
                ->orWhere('program_users.last_name', 'LIKE', "%{$search}%")
                ->where('value_sets.status','1')
                ->where('campaign_settings.wall_settings','1')
                ->where('user_nominations.level_1_approval', 1)
                ->orderByDesc('user_nominations.id')
                ->paginate(10);
        }
    }

    /**
     * @param Nomination $nomination
     * @return mixed
     */
    public function getSecondLevelWallUsers(Nomination $nomination, $search = NULL)
    {
        if($search === NULL) {

            return  $nomination->user_nomination()
            ->join('nominations', 'user_nominations.nomination_id', '=', 'nominations.id')
            ->join('value_sets', 'nominations.value_set', '=', 'value_sets.id')
            ->join('campaign_settings', 'campaign_types.id', '=', 'campaign_settings.campaign_id')
            ->where('value_sets.status','1')
            ->where('campaign_settings.wall_settings','1')
            ->where('user_nominations.level_2_approval', 1)->orderByDesc('user_nominations.id')->paginate(10);

           // return $nomination->user_nomination()->where('level_2_approval', 1)->orderByDesc('id')->get();
        } else {
            return $nomination
                ->user_nomination()
                ->join('program_users', 'user_nominations.user', '=', 'program_users.account_id')
                ->join('nominations', 'user_nominations.nomination_id', '=', 'nominations.id')
                ->join('value_sets', 'nominations.value_set', '=', 'value_sets.id')
                ->join('campaign_settings', 'campaign_types.id', '=', 'campaign_settings.campaign_id')
                ->where('program_users.first_name', 'LIKE', "%{$search}%")
                ->orWhere('program_users.last_name', 'LIKE', "%{$search}%")
                ->where('user_nominations.level_2_approval', 1)
                ->where('value_sets.status','1')
                ->where('campaign_settings.wall_settings','1')
                ->orderByDesc('user_nominations.id')
                ->paginate(10);
        }

    }

    public function getCampaignFirstLevelWallUsers(ValueSet $campaignSetting, $search = NULL)
    {
        if($search === NULL) {
           return $campaignSetting->usernomination()->where('user_nominations.level_1_approval',1)->orderByDesc('user_nominations.id')->get();
        } else {
            return $campaignSetting
                ->usernomination()
                ->join('program_users', 'user_nominations.user', '=', 'program_users.account_id')
                ->where('program_users.first_name', 'LIKE', "%{$search}%")
                ->orWhere('program_users.last_name', 'LIKE', "%{$search}%")
                ->where('user_nominations.level_1_approval', 1)->orderByDesc('user_nominations.id')->get();

        }
    }

    public function getCampaignSecondLevelWallUsers(ValueSet $campaignSetting, $search = NULL)
    {
        if($search === NULL) {
            return $campaignSetting->usernomination()->where('user_nominations.level_2_approval', 1)->orderByDesc('user_nominations.id')->get();

        } else {

            return $campaignSetting
                ->usernomination()
                ->join('program_users', 'user_nominations.user', '=', 'program_users.account_id')
                ->where('program_users.first_name', 'LIKE', "%{$search}%")
                ->orWhere('program_users.last_name', 'LIKE', "%{$search}%")
                ->where('user_nominations.level_2_approval', 1)->orderByDesc('user_nominations.id')->get();

        }

    }


    /**
     *
     * @return string|null
     * @throws \Exception
     */
    public function generateVerificationToken(): ?string
    {
        $token = str_random(50);

        if ($this->tokens_repository->getCount('token', $token) === 0) {
            return $token;
        }

        return $this->generateVerificationToken();
    }

    /**
     * @param $account
     *
     * @throws \Exception
     */
    public function sendmail($email,$subject,$message): void
    {
        $token = $this->generateVerificationToken();

            Mail::send(new \Modules\Nomination\Mails\SendMail($email,$token,$message,$subject));

    }

}
