<?php

namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;
use Modules\User\Models\ProgramUsers;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCampaignRole extends Model
{
	use SoftDeletes;
	protected $table = 'user_campaign_roles';
    protected $fillable = ['campaign_id','account_id','user_role_id'];
	
	public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
	
	public function programUserData() {
        return $this->belongsTo(ProgramUsers::class, 'account_id','account_id');
    }
}
