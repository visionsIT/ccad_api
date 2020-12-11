<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Program\Models\Currency::class, function (Faker $faker) {
    return [
        'name' => $faker->currencyCode
    ];
});
