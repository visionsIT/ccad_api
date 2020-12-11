<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Agency\Models\Agency::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
