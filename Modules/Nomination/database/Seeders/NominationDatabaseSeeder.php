<?php

namespace Modules\Nomination\database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Nomination\Models\AwardsLevel;
use Modules\Nomination\Models\Nomination;
use Modules\Nomination\Models\NominationType;
use Modules\Nomination\Models\SetApproval;
use Modules\Nomination\Models\UserNomination;
use Modules\Nomination\Models\ValueSet;
use Modules\Program\Models\Program;
use Modules\Nomination\Models\CampaignTypes;

class NominationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('CampaignTypesSeeder');

        $program = Program::where('name', 'AD Ports')->first();

        $value_set = ValueSet::create([
           'name' => 'AD Ports Core Values',
            'program_id' => $program->id // todo: reference to adPorts program
        ]);

        $type_1 = NominationType::create([
            'name' => 'Fair & Committed',
            'description' => 'We Will Always be Fair & Committed to our Employees, Customers and other Stakeholders',
            'logo' => 'http://backend.nomadicbees.com/img/fair.png',
            'value_set' => $value_set->id
        ]);

        $type_2 = NominationType::create([
            'name' => 'Ready to Respond',
            'description' => 'We will Always be Ready and Willing to Respond to Meet Our Customers’ Needs and Stakeholders Requirements',
            'logo' => 'http://backend.nomadicbees.com/img/respond.png',
            'featured' => '0',
            'value_set' => $value_set->id
        ]);

        $type_3 = NominationType::create([
            'name' => 'Innovation for Excellence',
            'description' => 'We Encourage and Nurture Innovations and care to Engage Employees, Suppliers, Customers and other Stakeholders on our Journey for Excellence',
            'logo' => 'http://backend.nomadicbees.com/img/innovation.png',
            'featured' => '0',
            'value_set' => $value_set->id
        ]);

        $type_4 = NominationType::create([
            'name' => 'Eager to Collaborate',
            'description' => 'We Shall Collaborate with All Internal and External Stakeholders* to get the Work Done for the Benefit of Our Customers and Partners',
            'logo' => 'http://backend.nomadicbees.com/img/eager.png',
            'featured' => '0',
            'value_set' => $value_set->id
        ]);

        $type_5 = NominationType::create([
            'name' => 'Safe, Secure & Sustainable',
            'description' => 'We are Committed to Maintain a Safe, Secure and Sustainable Business',
            'logo' => 'http://backend.nomadicbees.com/img/safe.png',
            'featured' => '0',
            'value_set' => $value_set->id
        ]);

        $nomination_level_1 = Nomination::create([
           'name' => 'Employee of the month',
            'status' => 'active',
            'value_set' => $value_set->id,
            'multiple_recipient' => 1,
            'approval_level' => 'approval_level_1',
            'reporting' => 'global_dashboard'
        ]);

        $nomination_level_2 = Nomination::create([
            'name' => 'Employee of the month Level 2',
            'status' => 'active',
            'value_set' => $value_set->id,
            'multiple_recipient' => 1,
            'approval_level' => 'approval_level_2',
            'reporting' => 'global_dashboard'
        ]);
        
        AwardsLevel::create([
           'name' => 'Recognition Points',
            'description' => '',
            'nomination_type_id' => $type_1->id,
            'points' => '500'
        ]);

        AwardsLevel::create([
            'name' => 'Recognition Points Type 2',
            'description' => '',
            'nomination_type_id' => $type_2->id,
            'points' => '1000'
        ]);

        AwardsLevel::create([
            'name' => 'Recognition Points Type 3',
            'description' => '',
            'nomination_type_id' => $type_3->id,
            'points' => '1500'
        ]);

        AwardsLevel::create([
            'name' => 'Recognition Points Type 4',
            'description' => '',
            'nomination_type_id' => $type_4->id,
            'points' => '2000'
        ]);

        AwardsLevel::create([
            'name' => 'Recognition Points Type 5',
            'description' => '',
            'nomination_type_id' => $type_5->id,
            'points' => '2500'
        ]);


        SetApproval::create([
           'level_1_approval_type' => 'users_groups',
            'level_1_permission' => 4,
            'level_1_user' => 1,
            'level_1_group' => 1,
            'level_2_approval_type' => 'permission',
            'nomination_id' =>  $nomination_level_1->id
        ]);

        SetApproval::create([
            'level_1_approval_type' => 'users_groups',
            'level_1_permission' => 1,
            'level_1_user' => 1,
            'level_1_group' => 1,
            'level_2_approval_type' => 'permission',
            'level_2_permission' => 4,
            'level_2_user' => 2,
            'level_2_group' => 1,
            'nomination_id' =>  $nomination_level_2->id
        ]);

    }
}

class CampaignTypesSeeder extends Seeder {

        public function run()
        {
          
            CampaignTypes::create([
               'campaign_type' => 'Anniversary Campaign',
                'status' => 1,
            ]);

            CampaignTypes::create([
               'campaign_type' => 'E-thank you campaign',
                'status' => 1,
            ]);

            CampaignTypes::create([
               'campaign_type' => 'IPV Campaign',
                'status' => 1,
            ]);

            CampaignTypes::create([
               'campaign_type' => 'Nomination Campaign',
                'status' => 1,
            ]);

            CampaignTypes::create([
               'campaign_type' => 'Submission Campaign',
                'status' => 1,
            ]);


        }

}