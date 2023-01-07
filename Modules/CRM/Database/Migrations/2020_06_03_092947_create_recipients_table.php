<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipients', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();

            $table->string('enquiries')->nullable();
            $table->string('enquiry_source')->nullable();
            $table->string('revenue')->nullable();
            $table->string('comments', 600)->nullable();

            $table->string('verification_code',600);
            $table->string('country')->nullable();
            $table->integer('country_code')->nullable();
            $table->string('birthdate')->nullable();
            $table->integer('birthmonth')->nullable();

            $table->enum('smart_routing',['enable','disable']);

            $table->softDeletes();

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
        Schema::dropIfExists('recipients');
    }
}
