<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEcardsSettingsInRewardsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reward_settings', function (Blueprint $table) {
            $table->enum('ecards_display', [0, 1])->default(0)->after('voucher_display');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rewards_settings', function (Blueprint $table) {
            //
        });
    }
}
