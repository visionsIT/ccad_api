<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserNominationsTeamProjectNameUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nominations', function (Blueprint $table) {
            $sql = "ALTER TABLE `user_nominations` ADD COLUMN `project_name` VARCHAR(191) NULL AFTER `level_2_approval`, ADD COLUMN `team_nomination` TINYINT UNSIGNED DEFAULT 0 NULL COMMENT '0 = User Nomination, 1 = Team Nomination' AFTER `project_name`; ";
                DB::connection()->getPdo()->exec($sql);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
