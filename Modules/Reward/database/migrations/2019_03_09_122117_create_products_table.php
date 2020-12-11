<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->float('value')->nullable();
            $table->string('image');
            $table->integer('seen')->default(0);;
            $table->string('quantity')->nullable();
            $table->float('base_price')->nullable();
            $table->string('likes')->nullable();
            $table->string('model_number')->nullable();
            $table->string('min_age')->nullable();
            $table->string('sku')->nullable();
            $table->string('type')->nullable();
            $table->string('validity')->nullable();
            $table->longText('description')->nullable();
            $table->longText('terms_conditions')->nullable();
            $table->integer('category_id')->unsigned();
            $table->integer('catalog_id')->unsigned();
            $table->integer('brand_id')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->foreign('catalog_id')->references('id')->on('product_catalogs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
