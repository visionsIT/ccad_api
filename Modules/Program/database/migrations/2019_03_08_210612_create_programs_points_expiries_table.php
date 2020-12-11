<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgramsPointsExpiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('programs_points_expiries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('program_id');
            $table->string('expiration_date');
            $table->boolean('return_expiry_points');

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
        Schema::dropIfExists('programs_points_expiries');
    }
}
