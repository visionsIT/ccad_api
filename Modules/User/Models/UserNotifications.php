<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;
use Modules\Nomination\Models\UserNomination;
use Modules\User\Models\NotificationsType;
use Modules\Reward\Models\ProductOrder;

class UserNotifications extends Model
{
    protected $fillable = ['receiver_account_id', 'sender_account_id', 'user_nomination_id', 'user_order_id', 'notification_type_id', 'mail_content', 'read_status', 'created_at', 'updated_at',];

    protected $table = 'user_notifications';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiver_account()
    {
        return $this->belongsTo(Account::class, 'receiver_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender_account()
    {
        return $this->belongsTo(Account::class, 'sender_account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_nomination()
    {
        return $this->belongsTo(UserNomination::class, 'user_nomination_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_order()
    {
        return $this->belongsTo(ProductOrder::class, 'user_order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notification_type()
    {
        return $this->belongsTo(NotificationsType::class, 'notification_type_id');
    }
}
