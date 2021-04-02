<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_template_types', function (Blueprint $table) {

            $table->increments('id');
            $table->string('template_name');
            $table->text('dynamic_code')->nullable();
            $table->longText('description');
            $table->enum('status', ['0', '1'])->default('1');
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
        Schema::dropIfExists('email_template_types');
    }
}
