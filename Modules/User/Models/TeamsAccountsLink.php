<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\Teams;
use Modules\Account\Models\Account;

class TeamsAccountsLink extends Model
{
    protected $table = "teams_accounts_link"; 
    protected $guarded = [];
    protected $fillable = [ 'account_id', 'team_id' ];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'id','account_id');
    }
    public function teams()
    {
        return $this->hasMany(Teams::class, 'id','team_id');
    }
}
