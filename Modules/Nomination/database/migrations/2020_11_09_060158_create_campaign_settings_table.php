<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id');
            $table->integer('send_multiple_status')->default(0)->comment('0 = single & 1 = multiple');
            $table->integer('approval_request_status')->default(0)->comment('0 = no approval required & 1 = approval required');
            $table->integer('level_1_approval')->default(0)->comment('0 mean not approved yet & 1 mean approved ');
            $table->integer('level_2_approval')->default(0)->comment('0 mean not approved yet & 1 mean approved ');
             $table->integer('budget_type')->default(1)->comment('1 for ripple budget & 2 for overall budget');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_settings');
    }
}
