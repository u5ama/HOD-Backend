<?php


namespace Modules\ThirdParty\Entities;


use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use Exception;
use GuzzleHttp\Client;
use Log;
use Config;

class OnlineDirectoryEntity extends AbstractEntity
{

    use UserAccess;


    public function __construct()
    {

    }

    public function getZocDocListingDetail($businessUrl)
    {
        try {
            $client = new Client([]);

            $serverUrl = Config::get('custom.Scrapper_Online_Directory_URL');
            $scrapperUrl = Config::get('custom.zocDocManualConnect');

            $url = $serverUrl . $scrapperUrl;

            $response = $client->request(
                'POST',
                $url,
                [
                    'form_params' => [
                        'url' => $businessUrl,
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            $records = $responseData['data'];

            if ($responseData['code'] == 200 && !empty($records['Name'])) {
                return $this->helpReturn("Zoc Doc Listing Detail", $records);

            }

            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info(" getLocationDetail " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function getHealthGradeListingDetail($businessUrl)
    {
        try {
//            $businessUrl = $request->get('businessUrl');

            $client = new Client([]);

            $serverUrl = Config::get('custom.Scrapper_Online_Directory_URL');
            $scrapperUrl = Config::get('custom.healthGradeManualConnect');

            $url = $serverUrl . $scrapperUrl;

            $response = $client->request(
                'POST',
                $url,
                [
                    'form_params' => [
                        'url' => $businessUrl,
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            $records = $responseData['data'];

            if ($responseData['code'] == 200 && !empty($records['Name'])) {
                return $this->helpReturn("Health Grade Listing Detail", $records);

            }

            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info(" getHealthGradeListingDetail " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function getRateMdsListingDetail($businessUrl)
    {
        try {
//            $businessUrl = $request->get('businessUrl');

            $client = new Client([]);

            $serverUrl = Config::get('custom.Scrapper_Online_Directory_URL');
            $scrapperUrl = Config::get('custom.rateMdsManualConnect');

            $url = $serverUrl . $scrapperUrl;

            $response = $client->request(
                'POST',
                $url,
                [
                    'form_params' => [
                        'url' => $businessUrl,
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            $records = $responseData['data'];

            if ($responseData['code'] == 200 && !empty($records['Name'])) {
                return $this->helpReturn("RateMds Listing Detail", $records);

            }

            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info(" getRateMdsListingDetail " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


}
