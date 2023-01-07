<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateNewColumnCrmSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_settings', function (Blueprint $table) {
            $table->string('email_negative_answer_setup_heading',255)->nullable()->after('sending_option');
            $table->text('email_negative_answer_setup_message')->nullable()->after('sending_option');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_settings', function (Blueprint $table) {
            $table->dropColumn('email_negative_answer_setup_heading');
            $table->dropColumn('email_negative_answer_setup_message');
        });
    }
}
