<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Account\Models\Account;
use Modules\Program\Presenters\ProgramsPointPresenter;

class ProgramsPoint extends Model
{
    use Presentable;

    protected $presenter = ProgramsPointPresenter::class;

    protected $fillable = [ 'value', 'program_id', 'transaction_type_id', 'description', 'balance', 'created_by_id' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class , 'created_by_id');
    }


}
