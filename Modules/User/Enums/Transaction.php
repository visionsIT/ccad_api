<?php namespace Modules\User\Enums;

class Transaction
{

    /**
     * @return array
     */
    public static function types(): array
    {
        return [
            'Account Closure',
            'Adjustment',
            'Order Adjustment',
            'Order Collection',
            'Person 2 Person Transaction',
            'Points Error',
            'Points Expiry',
            'Points Expiry - Return to client',
            'Programme Budget',
            'Programme Points',
            'Redemption',
        ];
    }
}
