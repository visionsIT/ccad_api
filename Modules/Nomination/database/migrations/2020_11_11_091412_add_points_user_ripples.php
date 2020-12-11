<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPointsUserRipples extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_ripples', function (Blueprint $table) {
            
            $table->string('points')->comment('point send by user')->after('reject_reason');
            $table->integer('point_type')->unsigned()->comment('1 for ripple, 2 for ripple')->after('points');
            $table->integer('campaign_id')->unsigned()->after('point_type');
            $table->integer('level_1_status')->unsigned()->comment('0 for pending, 1 for done')->after('campaign_id');
            $table->integer('level_2_status')->unsigned()->comment('0 for pending, 1 for done')->after('level_1_status');
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
