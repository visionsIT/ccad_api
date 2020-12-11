<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Reward\Models\SubProduct::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'value' => $faker->numberBetween(1, 10),
        'product_id' => factory(\Modules\Reward\Models\Product::class)->create()
    ];
});
