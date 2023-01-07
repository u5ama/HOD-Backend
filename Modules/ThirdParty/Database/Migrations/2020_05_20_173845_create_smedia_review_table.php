<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmediaReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('social_media_review', function (Blueprint $table) {
            $table->increments('review_id');

            $table->integer('social_media_id')->unsigned();
            $table->foreign('social_media_id')->references('id')->on('social_media_master')->onDelete('cascade');

            $table->string('rating', 255)->nullable();

            $table->string('reviewer',145)->nullable();
            $table->text('message')->nullable();
            $table->string('review_url',255)->nullable();
            $table->string ('review_date',255)->nullable(); //new

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
        Schema::dropIfExists('social_media_review');
    }
}
