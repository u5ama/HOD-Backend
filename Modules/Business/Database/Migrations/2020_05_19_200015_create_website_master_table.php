<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebsiteMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_master', function (Blueprint $table) {
            $table->increments('website_id');
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('business_id')->on('business_master')->onDelete('cascade');
            $table->string('website',355)->nullable();
            $table->tinyInteger('google_analytics')->default(1)->comment('1=Yes, 0=No');
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
        Schema::dropIfExists('website_master');
    }
}
