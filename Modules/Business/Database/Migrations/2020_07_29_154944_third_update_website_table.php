<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ThirdUpdateWebsiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_master', function (Blueprint $table) {
            $table->tinyInteger('google_adwords_deleted')->default(0)->comment('1=Yes, 0=No');
            $table->tinyInteger('gad_connected')->default(0)->comment('1=Yes, 0=No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_master', function (Blueprint $table) {
            $table->dropColumn('google_adwords_deleted');
            $table->dropColumn('gad_connected');
        });
    }
}
