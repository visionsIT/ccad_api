<?php

namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;
use Modules\User\Models\ProgramUsers;
use Modules\Reward\Models\ProductDenomination;


class ProductOrder extends Model
{
    protected $fillable = ['value','status','product_id','account_id' , 'first_name' , 'last_name' , 'email' , 'phone' , 'address' , 'city' , 'country' , 'is_gift' , 'comment','quantity','denomination_id'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Account::class);
    }


    //todo fix this relation
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProgramUsers::class, 'account_id');
    }

    public function denomination(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductDenomination::class, 'denomination_id');
    }


}
