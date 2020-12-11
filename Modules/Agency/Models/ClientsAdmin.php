<?php namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Account\Models\Account;
use Modules\Agency\Presenters\ClientsAdminPresenter;

class ClientsAdmin extends Model
{
    use SoftDeletes, Presentable;

    protected $presenter = ClientsAdminPresenter::class;

    protected $fillable = [ 'client_id', 'account_id' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
