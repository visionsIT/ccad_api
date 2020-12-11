<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('programs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('reference');
            $table->unsignedInteger('agency_id');
            $table->unsignedInteger('client_id');
            $table->string('currency_id');
            $table->string('theme');
            $table->string('sent_from_email');
            $table->string('contact_from_email');
            $table->string('google_analytics_id')->nullable();
            $table->string('google_tag_manager')->nullable();
            $table->string('modules');
            $table->string('user_start_date');
            $table->string('user_end_date');
            $table->string('staging_password');
            $table->enum('status', [ 'staging', 'live', 'closed' ]);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('agency_id')->references('id')->on('agencies')->onDelete('cascade');
//            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
}
