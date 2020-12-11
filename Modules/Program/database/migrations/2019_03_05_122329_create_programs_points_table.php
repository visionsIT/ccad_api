<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgramsPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('programs_points', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('value');
            $table->unsignedInteger('program_id');
            $table->unsignedInteger('transaction_type_id');
            $table->text('description');
            $table->integer('balance')->default(0);
            $table->unsignedInteger('created_by_id')->nullable();

            $table->timestamps();

            $table->foreign('created_by_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('program_id')->references('id')->on('programs')->onDelete('cascade');
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
        Schema::dropIfExists('programs_points');
    }
}
