<?php

namespace Modules\Agency\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Account\Models\Account;
use Modules\Agency\Models\Client;
use Modules\Agency\Models\Agency;
use Modules\Agency\Models\Catalogue;
use Modules\Agency\Models\ClientsAdmin;
use Illuminate\Database\Eloquent\Model;
use Modules\Program\Models\Program;

class AgencyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        factory(Agency::class, 5)->create();
        factory(Client::class, 5)->create();
        factory(ClientsAdmin::class, 5)->create();
        factory(Catalogue::class, 5)->create();

//        Permission::generateFor( (new Client())->getTable() );
//        Permission::generateFor( (new Catalogue())->getTable() );
//        Permission::generateFor( (new Agency())->getTable() );
//        Permission::generateFor( (new ClientAdmin())->getTable() );
//        Permission::generateFor( (new Program())->getTable() );

        $agency = Agency::create([
           'name' => 'ADPorts'
        ]);

        $client = Client::create([
           'agency_id' => $agency->id,
           'name' => 'AD Ports',
           'contact_name' => 'AD Ports',
           'contact_email' => 'client@visions.net.in',
           'logo' => 'logo',
        ]);

        $accont = Account::create([
            'name' => 'AD Ports Admin',
            'email' => $client->contact_email,
            'email_verified_at' => Carbon::now(),
            'password' => '123456',
            'contact_number' => '01000000',
            'type' => 'client_admin',
        ]);

        ClientsAdmin::create([
            'client_id' => $client->id,
            'account_id' => $accont->id
        ]);

        Program::create([
            'name' => 'AD Ports',
            'reference'=> 'AD_PORTS',
            'agency_id' => $agency->id,
            'client_id' => $client->id,
            'theme' => 'LightYellow',
            'currency_id' => 1,
            'sent_from_email' => 'program@visions.net.in',
            'contact_from_email' => 'program@visions.net.in',
            'modules' => serialize(['Nomination', 'Rewards']),
            'user_start_date' => Carbon::now(),
            'user_end_date' => Carbon::now()->add(1, 'year'),
            'staging_password' => bcrypt('123456'),
            'status' => 'live'
        ]);

    }
}
