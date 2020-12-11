<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserCampaignsBudget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_campaigns_budget', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_user_id')->unsigned();
            $table->foreign('program_user_id')->references('id')->on('program_users')->onDelete('cascade');
            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('value_sets')->onDelete('cascade');
            $table->integer('budget')->default('0');
            $table->string('description')->nullable();
            $table->integer('created_by_id')->nullable();
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
        Schema::dropIfExists('user_campaigns_budget');
    }
}
