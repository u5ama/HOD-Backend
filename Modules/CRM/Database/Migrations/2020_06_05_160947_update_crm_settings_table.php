<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCrmSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_settings', function (Blueprint $table) {
            $table->string('logo_image_src',255)->nullable()->after('sending_option');
            $table->string('background_image_src',255)->nullable()->after('sending_option');
            $table->string('top_background_color',255)->nullable()->after('sending_option');
            $table->string('review_number_color',255)->nullable()->after('sending_option');
            $table->string('star_rating_color',255)->nullable()->after('sending_option');

            $table->string('email_subject',255)->nullable()->after('sending_option');
            $table->string('email_heading',255)->nullable()->after('sending_option');
            $table->text('email_message')->nullable()->after('sending_option');

            $table->string('company_role',255)->nullable()->after('sending_option');
            $table->string('full_name',255)->nullable()->after('sending_option');
            $table->string('personal_avatar_src',255)->nullable()->after('sending_option');

            $table->string('sms_image',255)->nullable()->after('sending_option');
            $table->text('sms_message')->nullable()->after('sending_option');
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
            $table->dropColumn('logo_image_src');
            $table->dropColumn('background_image_src');
            $table->dropColumn('top_background_color');
            $table->dropColumn('review_number_color');
            $table->dropColumn('star_rating_color');

            $table->dropColumn('logo_image_src');
            $table->dropColumn('email_heading');
            $table->dropColumn('email_message');

            $table->dropColumn('company_role');
            $table->dropColumn('full_name');
            $table->dropColumn('personal_avatar_src');

            $table->dropColumn('sms_image');
            $table->dropColumn('sms_message');
        });
    }
}
