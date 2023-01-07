<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmediaLikeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_like', function (Blueprint $table) {
            $table->increments('like_id');
            $table->integer('social_media_id')->unsigned();
            $table->foreign('social_media_id')->references('id')->on('social_media_master')->onDelete('cascade');
            $table->bigInteger('count')->nullable();
            $table->date('like_date');
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
        Schema::dropIfExists('social_media_like');
    }
}
