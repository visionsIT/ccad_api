<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('tokens', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();
            $table->string('token');
            $table->string('type')->index()->comment('0 for password resets && 1 for activation links');
            $table->timestamp('created_at')->nullable();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
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
        Schema::dropIfExists('tokens');
    }
}
