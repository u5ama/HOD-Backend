<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_time')->nullable();
            $table->string('appointment_location')->nullable();
            $table->integer('appointment_service')->nullable();
            $table->integer('appointment_service_provider')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('appointment_time');
            $table->dropColumn('appointment_location');
            $table->dropColumn('appointment_service');
            $table->dropColumn('appointment_service_provider');
        });
    }
}
