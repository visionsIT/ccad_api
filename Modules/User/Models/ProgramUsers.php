<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;
use Modules\Program\Models\Program;
use Modules\CommonSetting\Models\CurrencyConversion;


class ProgramUsers extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function points()
    {
        return $this->hasOne(UsersPoint::class, 'user_id');
    }
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function currencyConversion(){
        return $this->hasOne(CurrencyConversion::class,'to_currency','country_id');
    }
}
