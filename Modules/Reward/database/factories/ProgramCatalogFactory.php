<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Reward\Models\ProductCatalog::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
