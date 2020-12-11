<?php namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Reward\Models\Product;

class UsersGoalItem extends Model
{

    protected $fillable = [ 'product_id', 'user_id' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(ProgramUsers::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
