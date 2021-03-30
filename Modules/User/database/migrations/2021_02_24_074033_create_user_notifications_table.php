<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('receiver_account_id');
            $table->unsignedInteger('sender_account_id')->nullable();
            $table->unsignedInteger('user_nomination_id')->nullable();
            $table->unsignedInteger('user_order_id')->nullable();
            $table->unsignedInteger('notification_type_id')->nullable();
            $table->text('mail_content');
            $table->enum('read_status', ['0','1'])->default('0');
            $table->timestamps();

            $table->foreign('receiver_account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('sender_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('user_nomination_id')->references('id')->on('user_nominations')->onDelete('set null');
            $table->foreign('user_order_id')->references('id')->on('product_orders')->onDelete('set null');
            $table->foreign('notification_type_id')->references('id')->on('notifications_type')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notifications');
    }
}
