<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRipplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ripples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id')->unsigned()->comment('Point sender User ID');
            $table->integer('receiver_id')->unsigned()->comment('Point receiver User ID');
            $table->integer('rejector_level1_id')->unsigned()->comment('User id of level 1 lead');
            $table->integer('rejector_level2_id')->unsigned()->comment('User id of level 2 lead');
            $table->integer('approval_level1_id')->unsigned()->comment('User id of level 1 lead');
            $table->integer('approval_level2_id')->unsigned()->comment('User id of level 2 lead');
            $table->integer('sender_type')->unsigned()->comment('0 for normal user, 1 for team lead');
            $table->longText('reject_reason')->nullable();
            $table->integer('is_active')->unsigned()->comment('1 for active, 0 for inactive');
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
        Schema::dropIfExists('user_ripples');
    }
}
