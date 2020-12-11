<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersEcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_ecards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ecard_id');
            $table->string('image_message');
            $table->unsignedInteger('sent_to');
            $table->unsignedInteger('sent_by');
            $table->bigInteger('points')->nullable();
            $table->enum('send_type', ['instant', 'schedule'])->default(null);
            $table->string('send_datetime')->nullable();
            $table->string('send_timezone')->nullable();
            $table->timestamps();

            $table->foreign('ecard_id')->references('id')->on('ecards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_ecards');
    }
}
