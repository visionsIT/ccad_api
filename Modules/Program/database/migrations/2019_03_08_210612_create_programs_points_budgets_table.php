<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgramsPointsBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('programs_points_budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('program_id');
            $table->boolean('is_disabled')->default(0);
            $table->boolean('return_to_budget')->default(0);
            $table->integer('points_drain_notification')->default(0);
            $table->json('notifiable_agency_admins')->nullable();
            $table->json('notifiable_client_admins')->nullable();

            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');

            $table->timestamps();
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
        Schema::dropIfExists('programs_points_budgets');
    }
}
