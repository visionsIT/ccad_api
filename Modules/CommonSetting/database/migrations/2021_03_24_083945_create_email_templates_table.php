<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            
            $table->increments('id');
            $table->integer('template_type_id')->unsigned();
            $table->foreign('template_type_id')->references('id')->on('email_template_types')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->longText('content');
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
        Schema::dropIfExists('email_templates');
    }
}
