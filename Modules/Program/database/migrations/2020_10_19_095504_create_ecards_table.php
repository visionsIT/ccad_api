<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('card_title');
            $table->string('card_image');
            $table->enum('status', [0, 1])->default(0);
            $table->enum('allow_points', [0, 1])->default(0);
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
        Schema::dropIfExists('ecards');
    }
}
