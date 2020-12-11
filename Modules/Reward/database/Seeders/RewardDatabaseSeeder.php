<?php

namespace Modules\Reward\database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Permission;
use Modules\Reward\Models\ProductCatalog;
use Modules\Reward\Models\ProductCategory;
use Modules\Reward\Models\SubProduct;

class RewardDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        factory(ProductCategory::class, 3)->create();
        factory(SubProduct::class, 3)->create();

    }
}
