<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInUserRipplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_ripples', function (Blueprint $table) {
            $table->integer('rejector_level1_id')->nullable()->change();
            $table->integer('rejector_level2_id')->nullable()->change();
            $table->integer('approval_level1_id')->nullable()->change();
            $table->integer('approval_level2_id')->nullable()->change();
            $table->integer('sender_type')->nullable()->change();
            $table->integer('level_1_status')->nullable()->change();
            $table->integer('level_2_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_ripples', function (Blueprint $table) {
            //
        });
    }
}
