<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('users_points', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('value');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('transaction_type_id');
            $table->text('description');
            $table->integer('balance')->default(0);
            $table->unsignedInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('created_by_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('program_users')->onDelete('cascade');
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
        Schema::dropIfExists('users_points');
    }
}
