<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('account_id')->unsigned();
            $table->integer('user_nomination_id')->unsigned();
			$table->tinyInteger('is_like')->default(0)->comment('1 for like & 0 for unlike');
            $table->timestamps();
			
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('user_nomination_id')->references('id')->on('user_nominations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_likes');
    }
}
