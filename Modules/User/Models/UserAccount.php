<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;

class UserAccount extends Model
{
    protected $guarded = [];

    protected $table = 'program_users_account';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(ProgramUsers::class, 'program_users_id');
    }

}
