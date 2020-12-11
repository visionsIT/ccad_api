<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRippleBudgetLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('ripple_budget_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ripple_budget')->default(0);
            $table->enum('type', ['0', '1'])->default('0')->comment("0 mean budget added and 1 mean budget deducted");
            $table->unsignedInteger('created_by_id')->nullable();
            $table->timestamps();
            $table->foreign('created_by_id')->references('id')->on('program_users')->onDelete('set null');
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
        Schema::dropIfExists('ripple_budget_log');
    }
}
