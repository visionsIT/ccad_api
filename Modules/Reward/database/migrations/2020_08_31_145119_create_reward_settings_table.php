<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaterewardSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('point_name');
            $table->string('insufficient_point');
            $table->string('gift_order_success');
            $table->string('physical_order_success');
            $table->enum('choose_goal_item', [0,1])->default(1);
            $table->enum('view_rewards', [0,1])->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reward_settings');
    }
}
