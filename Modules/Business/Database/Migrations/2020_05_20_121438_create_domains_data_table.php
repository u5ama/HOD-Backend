<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('domain')->nullable();
            $table->string('date')->nullable();
            $table->string('meta_data')->nullable();
            $table->string('headings')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('keywords_cloud')->nullable();
            $table->string('ratio_data')->nullable();
            $table->string('gzip')->nullable();
            $table->string('resolve')->nullable();
            $table->string('ip_can')->nullable();
            $table->string('links_analyser')->nullable();
            $table->string('broken_links')->nullable();
            $table->string('robots')->nullable();
            $table->string('sitemap')->nullable();
            $table->string('embedded')->nullable();
            $table->string('iframe')->nullable();
            $table->string('whois')->nullable();
            $table->string('mobile_fri')->nullable();
            $table->string('mobile_com')->nullable();
            $table->string('404_page')->nullable();
            $table->string('load_time')->nullable();
            $table->string('domain_typo')->nullable();
            $table->string('email_privacy')->nullable();
            $table->string('safe_bro')->nullable();
            $table->string('server_loc')->nullable();
            $table->string('speed_tips')->nullable();
            $table->string('analytics')->nullable();
            $table->string('w3c')->nullable();
            $table->string('encoding')->nullable();
            $table->string('indexed')->nullable();
            $table->string('alexa')->nullable();
            $table->string('social')->nullable();
            $table->string('visitors_loc')->nullable();
            $table->string('page_speed_insight')->nullable();
            $table->string('score')->nullable();
            $table->string('completed')->nullable();
            $table->string('source_insert')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domains_data');
    }
}
