<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCampaignSettingChnagesCampaignsettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_settings', function (Blueprint $table) {

            $table->enum('s_eligible_user_option', ['0', '1', '2'])->default('0')->comment('0 for all, 1 for L1, L2, L1&L2, 2 for multiple users or multiple groups')->after('max_point');

            $table->enum('s_level_option_selected', ['0', '1', '2'])->nullable()->comment('0 for level1, 1 for level2 and 2 for L1&L2')->after('s_eligible_user_option');

            $table->json('s_user_ids')->nullable()->after('s_level_option_selected');
            $table->json('s_group_ids')->nullable()->after('s_user_ids');
            $table->enum('receiver_users', ['0', '1'])->default('0')->after('s_group_ids');
            $table->json('receiver_group_ids')->nullable()->after('receiver_users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
