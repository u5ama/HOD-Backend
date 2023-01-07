<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_issues', function (Blueprint $table) {
            $table->increments('id');

            // foreign relationship of users table.
            $table->integer('business_id')->unsigned();
            $table->foreign('business_id')->references('business_id')->on('business_master');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // foreign relationship of sys_issue table.
            $table->unsignedBigInteger('issue_id');
            $table->foreign('issue_id')->references('issue_id')->on('sys_issues');

            // foreign relationship of sys_issue table.
            $table->integer('third_party_id')->unsigned();
            $table->foreign('third_party_id')->references('third_party_id')->on('third_party_master');

            // foreign relationship of sys_issue table.
            $table->integer('social_media_id')->unsigned();
            $table->foreign('social_media_id')->references('id')->on('social_media_master');

            $table->tinyInteger('is_deleted')->default(0);
            $table->string('module_type', 45)->default('local-marketing');

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
        Schema::dropIfExists('user_issues');
    }
}
