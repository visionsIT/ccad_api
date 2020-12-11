<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SsoLoginDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sso_login_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('entity_id')->nullable();
            $table->string('sso_url')->nullable();
            $table->string('sl_url')->nullable();
            $table->longText('x509')->nullable();
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
        Schema::dropIfExists('sso_login_details');
    }
}
