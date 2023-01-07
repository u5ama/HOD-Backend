<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('delete_by')->nullable()->after('account_status');
            $table->timestamp('deleted_at')->nullable()->after('delete_by');
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive')->after('delete_by');
            $table->tinyInteger('status_change_by')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('delete_by');
            $table->dropColumn('deleted_at');
            $table->dropColumn('status');
            $table->dropColumn('status_change_by');
        });
    }
}
