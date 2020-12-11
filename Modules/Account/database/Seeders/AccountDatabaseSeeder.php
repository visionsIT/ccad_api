<?php

namespace Modules\Account\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Account\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Permission;
use Spatie\Permission\Models\Role;

class AccountDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        factory(Account::class, 5)->create();

        $this->generatePermissions();
        $this->assignPermissionsToRole();


        // $this->call("OthersTableSeeder");
    }

    private function generatePermissions()
    {
        Permission::create([
            'value' => 'Enable login',
            'name' => 'enable_login',
            'table_name' => 'General',
            'guard_name' => 'api',
            'description' => 'Enable log in'
        ]);
        Permission::create([
            'value' => 'Local Overview Reporting',
            'name' => 'local_overview_reporting',
            'table_name' => 'General',
            'guard_name' => 'api',
            'description' => 'Access to engagement dashboard reports for all user groups below them in the structure'
        ]);
        Permission::create([
            'value' => 'Global Overview Reporting',
            'name' => 'global_overview_reporting',
            'table_name' => 'General',
            'guard_name' => 'api',
            'description' => 'Access to engagement dashboard for all user groups'
        ]);
        Permission::create([
            'value' => 'Line Manager approval',
            'name' => 'line_manager_approval',
            'table_name' => 'Recognitions module',
            'guard_name' => 'api',
            'description' => 'First level approval rights. Any action requiring line manager approval will find the first appropriate line manager in the group structure.',
        ]);

        Permission::create([
            'value' => 'Department Manager approval',
            'name' => 'department_manager_approval',
            'table_name' => 'Recognitions module',
            'guard_name' => 'api',
            'description' => 'Second level approval rights. Any action requiring department head approval will find the first appropriate department head in the group structure.',
        ]);

        Permission::create([
            'value' => 'Local recognition reporting',
            'name' => 'local_recognition_reporting',
            'table_name' => 'Recognitions module',
            'guard_name' => 'api',
            'description' => 'Access to dashboard reports for all user groups below then in the structure.',
        ]);

        Permission::create([
            'value' => 'Global recognition reporting',
            'name' => 'global_recognition_reporting',
            'table_name' => 'Recognitions module',
            'guard_name' => 'api',
            'description' => 'Access to dashboard reports for all user groups',
        ]);

        Permission::create([
            'value' => 'Order Rewards',
            'name' => 'order_rewards',
            'table_name' => 'Rewards module',
            'guard_name' => 'api',
            'description' => 'User can order rewards if they have sufficient points available',
        ]);

        Permission::create([
            'value' => 'Local Rewards Reporting',
            'name' => 'local_rewards_reporting',
            'table_name' => 'Rewards module',
            'guard_name' => 'api',
            'description' => 'Access to dashboard reports for all user groups below them in the structure',
        ]);

        Permission::create([
            'value' => 'Global Rewards Reporting',
            'name' => 'global_rewards_reporting',
            'table_name' => 'Rewards module',
            'guard_name' => 'api',
            'description' => 'Access to dashboard reports for all user groups',
        ]);

        Permission::create([
            'value' => 'Approve claims',
            'name' => 'approve_claims',
            'table_name' => 'Performance module',
            'guard_name' => 'api',
            'description' => 'Allows the user access to the admin area to review and approve programme claims',
        ]);

        Permission::create([
            'value' => 'Local Performance Reporting',
            'name' => 'local_performance_reporting',
            'table_name' => 'Performance module',
            'guard_name' => 'api',
            'description' => 'Access to dashboard reports for all user groups below in the structure',
        ]);

        Permission::create([
            'value' => 'Global Performance Reporting',
            'name' => 'global_performance_reporting',
            'table_name' => 'Performance module',
            'guard_name' => 'api',
            'description' => 'Access to dashboard reports for all user groups',
        ]);

    }

    private function assignPermissionsToRole()
    {

        foreach (Role::all() as $role)
        {
            $role->givePermissionTo(['enable_login', 'order_rewards']);
        }
    }

}
