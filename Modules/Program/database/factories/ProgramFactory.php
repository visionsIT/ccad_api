<?php

use Faker\Generator as Faker;

$factory->define(\Modules\Program\Models\Program::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'reference' => $faker->sentence,
        'agency_id' => factory(\Modules\Agency\Models\Agency::class)->create(),
        'client_id' => factory(\Modules\Agency\Models\Client::class)->create(),
        'currency_id' => factory(\Modules\Program\Models\Currency::class)->create(),
        'theme' => $faker->colorName,
        'contact_from_email' => $faker->unique()->safeEmail,
        'sent_from_email' => $faker->unique()->safeEmail,
        'staging_password' => bcrypt('password'),
        'status' => 'staging',
        'google_tag_manager' => '',
        'google_analytics_id' => '',
        'modules' => '',
        'user_start_date' => $faker->dateTime,
        'user_end_date' => $faker->dateTime,
    ];
});
