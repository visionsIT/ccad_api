<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Agency\Models\Catalogue::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
