<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_history', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            // foreign relationship of users table.
            $table->integer('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('recipients');

            $table->tinyInteger('sms_count')->nullable();
            $table->tinyInteger('email_count')->nullable();
            $table->timestamp('sms_last_sent')->nullable();
            $table->timestamp('email_last_sent')->nullable();

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
        Schema::dropIfExists('setting_history');
    }
}
