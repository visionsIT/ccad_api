<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Agency\Models\Client::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'agency_id' => factory(\Modules\Agency\Models\Agency::class)->create(),
        'contact_email' => $faker->unique()->safeEmail,
        'contact_name' => $faker->name,
    ];
});
