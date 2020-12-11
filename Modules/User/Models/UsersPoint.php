<?php namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Account\Models\Account;
use Modules\Program\Presenters\ProgramsPointPresenter;

class UsersPoint extends Model
{
    use Presentable;

    protected $presenter = ProgramsPointPresenter::class;

    protected $fillable = [ 'value', 'user_id', 'user_nominations_id', 'product_order_id', 'transaction_type_id', 'description', 'balance', 'attachment', 'created_by_id' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'created_by_id');
    }


}
