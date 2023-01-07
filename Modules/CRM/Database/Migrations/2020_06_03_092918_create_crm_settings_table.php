<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_settings', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->enum('enable_get_reviews', ['Yes', 'No'])->nullable();
            $table->enum('smart_routing', ['Enable', 'Disable'])->nullable();

            $table->tinyInteger('sending_option')->nullable();

            $table->longText('customize_email')->nullable();
            $table->longText('customize_sms')->nullable();

            $table->string('review_site')->nullable();

            $table->enum('reminder', ['Yes', 'No'])->nullable();

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
        Schema::dropIfExists('crm_settings');
    }
}
