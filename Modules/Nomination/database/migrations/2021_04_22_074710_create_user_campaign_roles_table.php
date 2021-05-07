<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCampaignRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_campaign_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->Integer('campaign_id')->unsigned();
            $table->Integer('account_id')->unsigned();
			$table->enum('type', ['L1', 'L2'])->nullable();
			$table->integer('user_role_id')->nullable();
			$table->integer('is_active')->default('1');
			$table->integer('is_deleted')->default('0');
            $table->timestamps();
			$table->softDeletes();
			$table->foreign('campaign_id')->references('id')->on('value_sets')->onDelete('cascade');
			$table->foreign('account_id')->references('account_id')->on('program_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_campaign_roles');
    }
}
