<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReviewsRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews_requests', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('recipient_id')->unsigned()->nullable();
            $table->foreign('recipient_id')->references('id')->on('recipients');

            $table->text('message')->nullable();
            $table->enum('site',['FB','YP','GP','TA','ZD','VT','RM','HG'])->nullable();
            $table->date('date_sent')->nullable();

            $table->string('type',20)->nullable();

            $table->text('message_body')->nullable();
            $table->enum('status',['SENT','READY_TO_SEND'])->nullable();
            $table->string('flag')->nullable();

            $table->string('review_status')->default('false');

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
        Schema::dropIfExists('reviews_requests');
    }
}
