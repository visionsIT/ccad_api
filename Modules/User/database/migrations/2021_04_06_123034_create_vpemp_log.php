<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVpempLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_vp_emp_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_account_id');
            $table->integer('previous_vp_emp')->nullable();
            $table->unsignedInteger('new_vp_emp_number');
            $table->foreign('user_account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('new_vp_emp_number')->references('id')->on('accounts')->onDelete('cascade');
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
        Schema::dropIfExists('user_vp_emp_log');
    }
}
