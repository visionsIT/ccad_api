<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('parent')->unsigned()->nullable();
            $table->integer('catalog')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('product_categories', function($table) {
//            $table->foreign('parent')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('catalog')->references('id')->on('product_catalogs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
}
