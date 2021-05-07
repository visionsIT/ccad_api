<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddL1ApproverToCampaignSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_settings', function (Blueprint $table) {
            $table->enum('l1_approver', ['0','1'])->default('0')->comment('0 for linked users, 1 for specific users')->after('certificate_image');
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
