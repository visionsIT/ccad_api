<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEcardScheduler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaign_settings', function (Blueprint $table) {

            $table->enum('ecard_scheduler', ['0', '1'])->nullable()->comment('0 for scheduler OFF and 1 for scheduler ON')->after('wall_settings');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
