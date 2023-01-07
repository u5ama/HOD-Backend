<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleAdwordsStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_adwords_stats', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('business_id')->on('business_master');
            $table->string('clicks')->nullable();
            $table->string('impressions')->nullable();
            $table->string('conversions')->nullable();
            $table->string('impression_share')->nullable();
            $table->string('adsSpend')->nullable();
            $table->string('cost_per_conversions')->nullable();

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
        Schema::dropIfExists('google_adwords_stats');
    }
}
