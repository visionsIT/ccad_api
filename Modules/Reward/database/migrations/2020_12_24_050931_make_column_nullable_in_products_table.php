<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeColumnNullableInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
           // $table->string('validity')->nullable()->change();
           // $table->longText('terms_conditions')->nullable()->change();
              DB::statement('ALTER TABLE `products` MODIFY `validity` VARCHAR(255) NULL;');
              DB::statement('ALTER TABLE `products` MODIFY `terms_conditions` LONGTEXT NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
}
