<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCustomerFormSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_form_settings', function (Blueprint $table) {

            $table->string('headColor')->nullable();
            $table->string('headFontSize')->nullable();
            $table->string('allFontFamily')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_form_settings', function (Blueprint $table) {

            $table->dropColumn('headColor');
            $table->dropColumn('headFontSize');
            $table->dropColumn('allFontFamily');

        });
    }
}
