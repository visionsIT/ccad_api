<?php namespace Modules\Reward\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;

class ProductsAccountsSeen extends Model
{

    protected $table = 'products_accounts_seen';

    protected $fillable = [ 'account_id', 'product_id' ];

}

