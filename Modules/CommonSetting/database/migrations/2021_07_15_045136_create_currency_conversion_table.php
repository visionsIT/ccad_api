<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyConversionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_conversion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_currency');
            $table->unsignedInteger('to_currency');
            $table->string('conversion');
            $table->enum('status',['0','1','2'])->default('1')->comment('0 => delete, 1 => active, 2 => inactive');
            $table->timestamps();
            $table->foreign('from_currency')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('to_currency')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_conversion');
    }
}
