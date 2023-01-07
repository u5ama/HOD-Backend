<?php
namespace Modules\GoogleAnalytics\Entities;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class Google {

    protected $client;

    protected $service;

    function __construct() {
        /* Get config variables */
        $client_id = Config::get('global.client_id');
        $service_account_name = Config::get('global.service_account_name');
        $key = Config::get('global.api_key');//you can use later

        $this->client = new \Google_Client();

        $this->client->setApplicationName("netblaze");
        $this->service = new \Google_Service_Books($this->client);//Test with Books Service
    }

    public function getBooks(){
        $optParams = array('filter' => 'free-ebooks');
        $results = $this->service->volumes->listVolumes('Henry David Thoreau', $optParams);
    }
}
