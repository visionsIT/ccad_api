<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCampaignTypeValueSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('value_sets', function (Blueprint $table) {
            $table->unsignedInteger('campaign_type_id')->default('4');
            $table->foreign('campaign_type_id')->references('id')->on('campaign_types');
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



