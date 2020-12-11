<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(\Modules\Agency\Database\Seeders\AgencyDatabaseSeeder::class);
        $this->call(\Modules\Program\Database\Seeders\ProgramDatabaseSeeder::class);
        $this->call(\Modules\Account\Database\Seeders\AccountDatabaseSeeder::class);
        $this->call(\Modules\Reward\database\Seeders\RewardDatabaseSeeder::class);
        $this->call(\Modules\Nomination\database\Seeders\NominationDatabaseSeeder::class);
    }
}
