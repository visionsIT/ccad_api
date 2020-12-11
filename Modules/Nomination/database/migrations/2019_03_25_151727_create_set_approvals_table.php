<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSetApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('set_approvals', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('level_1_approval_type',['permission','users_groups']);
            $table->string('level_1_permission')->nullable();
            $table->string('level_1_user')->nullable();
            $table->string('level_1_group')->nullable();
            $table->enum('level_2_approval_type',['permission','users_groups'])->nullable();
            $table->string('level_2_permission')->nullable();
            $table->string('level_2_user')->nullable();
            $table->string('level_2_group')->nullable();
            $table->integer('nomination_id')->unsigned()->unique();
            $table->timestamps();

            $table->foreign('nomination_id')->references('id')->on('nominations')->onDelete('cascade');

        });

        Schema::enableForeignKeyConstraints();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('set_approvals');
    }
}
