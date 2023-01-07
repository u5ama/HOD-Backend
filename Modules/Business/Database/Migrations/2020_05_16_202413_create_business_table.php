<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('business_master', function (Blueprint $table) {
                $table->increments('business_id');

                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');

                $table->string('business_name');
                $table->string('business_location')->nullable();
                $table->string('phone')->nullable();
                $table->string('website')->nullable();;
                $table->string('address')->nullable();;
                $table->string('city')->nullable();
                $table->string('zip_code')->nullable();
                // foreign relationship of users table.
                $table->string('state')->nullable();;
                $table->string('country')->nullable();
                $table->string('avatar')->nullable();
                $table->string('logo')->nullable();
                $table->string('user_agent')->nullable();

                $table->string('business_status')->nullable();
                $table->integer('discovery_status')->default(0)->comment('0-Not started, 1-Success, 2-Failed, 3-Working', '4-Business Process', '5-Web Process', '6-Reviews Process');
                // foreign relationship of users table.
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
        Schema::dropIfExists('business_master');
    }
}
