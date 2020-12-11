<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Reward\Models\Product::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'value' => $faker->numberBetween(1, 10),
        'image' => $faker->imageUrl(),
        'quantity' => '100',
        'category_id' => factory(\Modules\Reward\Models\ProductCategory::class)->create(),
        'catalog_id' => factory(\Modules\Reward\Models\ProductCatalog::class)->create()
    ];
});
