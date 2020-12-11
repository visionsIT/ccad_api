<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamAcocuntLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams_accounts_link', function (Blueprint $table) {
            $sql = "CREATE TABLE `teams_accounts_link` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `account_id` int(10) unsigned NOT NULL,
                  `team_id` int(10) unsigned NOT NULL,
                  KEY `id` (`id`),
                  KEY `account_id` (`account_id`),
                  KEY `team_id` (`team_id`),
                  CONSTRAINT `teams_accounts_link_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
                  CONSTRAINT `teams_accounts_link_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
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
        Schema::dropIfExists('team_acocunt_link');
    }
}
