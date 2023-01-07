<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThirdPartyMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_party_master', function (Blueprint $table) {
            $table->increments('third_party_id');

            $table->integer('business_id')->unsigned()->nullable(); // new
            $table->foreign('business_id')->references('business_id')->on('business_master');
            $table->string('type',100)->nullable();
            $table->string('name',100)->nullable();
            $table->string('website',999)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('fax',20)->nullable();
            $table->string('street',255)->nullable();
            $table->string('city',255)->nullable();
            $table->string('zipcode',10)->nullable();
            $table->string('state',255)->nullable();
            $table->string('country',255)->nullable();
            $table->integer('location_id')->nullable(); // not required
            $table->string('page_url',500)->nullable();
            $table->string('add_review_url',1000)->nullable();
            $table->float('review_count')->nullable(); // Review
            $table->float('average_rating')->nullable(); // rating

            $table->integer('is_manual_connected')->default(0);
            $table->tinyInteger('is_manual_deleted')->default(0); //new

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
        Schema::dropIfExists('third_party_master');
    }
}
