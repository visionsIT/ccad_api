<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCampaignSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_settings', function (Blueprint $table) {
			$table->tinyInteger('like_flag')->default(0)->comment('1 for on & 0 for off')->after('wall_settings');
			$table->tinyInteger('comment_flag')->default(0)->comment('1 for on & 0 for off')->after('like_flag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaign_settings', function (Blueprint $table) {
            //
        });
    }
}
