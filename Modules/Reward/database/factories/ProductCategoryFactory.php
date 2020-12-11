<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Reward\Models\ProductCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'parent' => 0,
        'catalog'=> factory(\Modules\Reward\Models\ProductCatalog::class)->create()
    ];
});
