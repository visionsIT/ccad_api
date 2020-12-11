<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominationDeclinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('nomination_declines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description')->unique();
            $table->integer('nomination_id')->unsigned();
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
        Schema::dropIfExists('nomination_declines');
    }
}
