<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Agency\Models\ClientsAdmin::class, function (Faker $faker) {
    return [
        'client_id'  => factory(\Modules\Agency\Models\Client::class)->create(),
        'account_id' => factory(\Modules\Account\Models\Account::class)->create(),
//        'role'       => 1,
    ];
});
