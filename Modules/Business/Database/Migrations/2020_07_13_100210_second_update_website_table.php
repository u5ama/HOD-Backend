<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SecondUpdateWebsiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_master', function (Blueprint $table) {
            $table->tinyInteger('ga_connected')->default(0)->comment('1=Yes, 0=No');
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
            $table->dropColumn('ga_connected');
        });
    }
}
