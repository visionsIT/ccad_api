<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('active_url')->nullable();
            $table->string('not_active_url')->nullable();
            $table->string('points')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_types');
    }
}
