<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Reward\Models\ProductOrder::class, function (Faker $faker) {
    return [
        'name' => $faker->title
    ];
});
