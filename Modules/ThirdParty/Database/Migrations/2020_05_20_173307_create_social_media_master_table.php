<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialMediaMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_master', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('business_id')->on('business_master');

            $table->string('type',100)->nullable();

            $table->string('name',100)->nullable();
            $table->integer('followers')->nullable();
            $table->string('website',255)->nullable();
            $table->string('phone',100)->nullable();
            $table->string('fax',20)->nullable();
            $table->string('street',255)->nullable();
            $table->string('city',255)->nullable();
            $table->string('zipcode',10)->nullable();
            $table->string('state',255)->nullable();
            $table->string('country',255)->nullable();
            $table->string('cover_photo',512)->nullable();
            $table->string('profile_photo',512)->nullable();

            $table->string('page_access_token',700)->nullable();
            $table->bigInteger('page_id')->nullable();
            $table->string('page_url',500)->nullable();
            $table->string('add_review_url',1000)->nullable();
            $table->string('average_rating',255)->nullable();
            $table->string('page_reviews_count',255)->nullable();
            $table->string('page_likes_count',255)->nullable();

            $table->string('access_token',1000)->nullable();
            $table->integer('is_manual_connected')->default(1);
            $table->tinyInteger('is_manual_deleted')->default(0);

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
        Schema::dropIfExists('social_media_master');
    }
}
