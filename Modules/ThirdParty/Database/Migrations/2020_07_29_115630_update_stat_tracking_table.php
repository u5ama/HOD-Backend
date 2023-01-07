<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStatTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stat_tracking', function (Blueprint $table) {
            $table->unsignedBigInteger('google_adwords_id')->nullable();
            $table->foreign('google_adwords_id')->references('id')->on('google_adwords_master');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stat_tracking', function (Blueprint $table) {

        });
    }
}
