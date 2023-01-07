<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerFormSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_form_settings', function (Blueprint $table) {
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
        Schema::dropIfExists('customer_form_settings');
    }
}
