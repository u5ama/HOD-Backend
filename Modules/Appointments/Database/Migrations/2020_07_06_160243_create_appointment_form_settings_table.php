<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentFormSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_form_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->nullable();

            $table->string('type')->nullable();

            $table->string('width')->nullable();
            $table->string('height')->nullable();

            $table->string('fontSize')->nullable();
            $table->string('fontColor')->nullable();

            $table->string('backgroundColor')->nullable();

            $table->string('labelColor')->nullable();
            $table->string('labelFontSize')->nullable();

            $table->string('borderColor')->nullable();
            $table->string('btnWidth')->nullable();
            $table->string('btnHeight')->nullable();

            $table->string('headColor')->nullable();
            $table->string('headFontSize')->nullable();
            $table->string('headingText')->nullable();

            $table->string('allFontFamily')->nullable();

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
        Schema::dropIfExists('appointment_form_settings');
    }
}
