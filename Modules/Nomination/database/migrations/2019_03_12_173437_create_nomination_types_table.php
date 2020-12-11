<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('nomination_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->integer('featured')->default('1');
            $table->unsignedInteger('value_set');

            $table->string('times')->nullable();
            $table->string('active_url')->nullable();
            $table->string('not_active_url')->nullable();
            $table->string('points')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('value_set')->references('id')->on('value_sets')->onDelete('cascade');
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
        Schema::dropIfExists('nomination_types');
    }
}
