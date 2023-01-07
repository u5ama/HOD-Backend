<?php

namespace Modules\ThirdParty\Entities;

use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use Exception;
use FuzzyWuzzy\Fuzz;
use GuzzleHttp\Client;
use Modules\Business\Entities\BusinessEntity;
use Modules\ThirdParty\Models\StatTracking;
use Modules\ThirdParty\Models\ThirdPartyMaster;
use Modules\ThirdParty\Models\TripadvisorMaster;
use Modules\ThirdParty\Models\TripadvisorReview;
use Request;
use SKAgarwal\GoogleApi\PlacesApi;
use Log;
use DB;
use Config;

class GooglePlaceEntity extends AbstractEntity
{
    use UserAccess;

    public function getFirstPlaceID($request)
    {
        $searchFor = $request->get('name');
        if(!empty($request->get('business_address')))
        {
            $searchFor = $searchFor . ' ' . $request->get('business_address');
        }

        if ($searchFor != '') {
            try {
                Log::info("Entered in try ");
                $googlePlaces = new PlacesApi('AIzaSyCCuo6lEQ9qXyD15di5gEd6tHuWNfamC0A');

                Log::info("Got KEy ");
                $results = $googlePlaces->textSearch($searchFor);

                Log::info("$results");
                $placeResultData['place_id'] = $results['results'][0]['place_id'];

                $googlePlaceResultDetail = $this->getPlaceResult($placeResultData['place_id']);
                Log::info("Got place details");
                Log::info("$googlePlaceResultDetail");
                return $googlePlaceResultDetail;

            } catch (Exception $e) {
                Log::info(" getFirstPlaceID -> " . $e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        } else {
            return $this->helpError(2, 'Place string is missing.');
        }
    }

    public function getPlaceResult($placeId)
    {
        $placeFor = $placeId;

        if ($placeFor != '') {
            try {
                $googlePlaces = new PlacesApi('AIzaSyCCuo6lEQ9qXyD15di5gEd6tHuWNfamC0A');
                $results = $googlePlaces->placeDetails($placeFor);

                $placeResultData['type'] = 'Google Places';
                $placeResultData['name'] = $results['result']['name'];
                $placeResultData['average_rating'] = !empty($results['result']['rating']) ? $results['result']['rating'] : '';

                $placeResultData['website'] = !empty($results['result']['website']) ? $results['result']['website'] : '';
                $placeResultData['phone'] = !empty($results['result']['international_phone_number']) ? $results['result']['international_phone_number'] : '';

                $placeResultData['street'] = $results['result']['vicinity'];

                if (!empty($results['result']['address_components'])) {
                    foreach ($results['result']['address_components'] as $addressComponents) {
                        if (!empty($addressComponents['types'][0])) {
                            $addressType = $addressComponents['types'][0];
                            $addressName = $addressComponents['long_name'];

                            if ($addressType == 'country') {
                                $placeResultData['country'] = $addressName;
                            }
                            if ($addressType == 'locality') {
                                $placeResultData['city'] = $addressName;
                            } elseif ($addressType == 'administrative_area_level_1') {
                                $placeResultData['state'] = $addressName;
                            } elseif ($addressType == 'postal_code') {
                                $placeResultData['zipcode'] = $addressName;
                            } elseif ($addressType == 'postal_code_suffix' && !empty($placeResultData['zipcode'])) {
                                $placeResultData['zipcode'] = $placeResultData['zipcode'] . '-' . $addressName;
                            }
                        }
                    }
                }

                $placeResultData['page_url'] = !empty($results['result']['url']) ? $results['result']['url'] : '';
                $placeResultData['place_id'] = !empty($results['result']['place_id']) ? $results['result']['place_id'] : '';
                $placeResultData['reviews'] = !empty($results['result']['reviews']) ? $results['result']['reviews'] : '';

                if(!empty($results['result']['reviews'][0]))
                {
                    $placeReviewData['reviewer'] = $results['result']['reviews'][0]['author_name'];
                    $placeResultData['message'] = $results['result']['reviews'][0]['text'];
                    $placeReviewData['review_url'] = $results['result']['reviews'][0]['author_url'];
                    $placeReviewData['review_date'] = $results['result']['reviews'][0]['relative_time_description'];
                }

                if(!empty($placeReviewData['review_url']))
                {
                    $uri_path = trim(parse_url($placeReviewData['review_url'], PHP_URL_PATH), '/');
                    $uri_segments = explode('/', $uri_path);
                    $placeReviewData['review_unique_identifier'] = $uri_segments[2];
                }

                /**
                 * Parse url from string and add http:// before website url
                 */

                if (!empty($GooglePlaceResult['third_party_id'])) {
                    $reviewData = [];
                    $i = 0;
                    $reviewData['third_party_id'] = $GooglePlaceResult['third_party_id'];
                    $reviewData['reviewer'] = $results['result']['reviews'][0]['author_name'];
                    $reviewData['message'] = $results['result']['reviews'][0]['text'];
                    $reviewData['rating'] = $results['result']['reviews'][0]['rating'];
                    $reviewData['review_url'] = $results['result']['reviews'][0]['author_url'];
                    $reviewData['review_date'] = $results['result']['reviews'][0]['relative_time_description'];
                    $uri_path = trim(parse_url($reviewData['review_url'], PHP_URL_PATH), '/');
                    $uri_segments = explode('/', $uri_path);
                    $reviewData['review_unique_identifier'] = $uri_segments[2];
                }

                   return $this->helpReturn('Results are.', $placeResultData);
            } catch (Exception $e) {
                Log::info(" getPlaceResult -> " . $e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        } else {
            return $this->helpError(2, 'Place Id is missing.');
        }
    }

    public function getMapCoordinates($request)
    {
        try {
            Log::info('inside map coordinates');
            $businessObj = new BusinessEntity();

            if (isset($request->token)) {
                $checkPoint = $this->setCurrentUser($request->token)->userAllow();
            } else {

                $userRecord = User::where('email', $request->email)->first();
                $token = JWTAuth::fromUser($userRecord);
                $checkPoint = $this->setCurrentUser($token)->userAllow();
            }

            // user is not found.
            if ($checkPoint['_metadata']['outcomeCode'] != 200) {
                return $checkPoint;
            }
            $user = $checkPoint['records'];

            $businessResult = $businessObj->userSelectedBusiness($user);
            $businessResult = $businessResult['records'];

            $keyword = $request->get('keyword');
            $appendFinalArray = [];
            $client = new Client([]);

            $apiKey = config::get('apikeys.googleApi');
            $result = [];

            $response = $client->request(
                'GET',
                'https://maps.googleapis.com/maps/api/geocode/json',
                [
                    'query' => [
                        'key' => $apiKey,
                        'address' => $keyword,

                    ],
                    'verify' => false,
                ]
            );

            if($response->getStatusCode() == 200) {
                $responseData = json_decode($response->getBody()->getContents(), true);
                if(isset($responseData['status']) && $responseData['status'] == 'OK'){
                    $MapCoordinates = array_filter($responseData);
                    $result['latitude'] = $MapCoordinates['results'][0]['geometry']['location']['lat'];

                    $result['longitude'] = $MapCoordinates['results'][0]['geometry']['location']['lng'];
                    if (!empty($result['latitude'] && $result['longitude'])) {
                        $placeresult = $this->getPlaceSearch($result['latitude'], $result['longitude'], $keyword);
                        $graphDataAppendArray = [];
                        $businessDataAppendArray = [];
                        if ($placeresult['_metadata']['outcomeCode'] == 200) {
                            foreach ($placeresult['records'] as $row) {
                                $url = '';
                                if (isset($row['photos'])) {

                                    $phoneRefernce = $row['photos'][0]['photo_reference'];
                                    $height = $row['photos'][0]['height'];
                                    $width = $row['photos'][0]['width'];
                                    $url = "https://maps.googleapis.com/maps/api/place/photo?photoreference=$phoneRefernce&key=AIzaSyDIPbhytGCc5Oc6u41jD3n25AeVTfDXezM&maxheight=$height&maxwidth=$width";
                                }
                                $lat = isset($row['geometry']['viewport']['northeast']['lat']) ? $row['geometry']['viewport']['northeast']['lat'] : '';
                                $lng = isset($row['geometry']['viewport']['northeast']['lng']) ? $row['geometry']['viewport']['northeast']['lng'] : '';

                                $graphDataAppendArray[] = [
                                    'name' => isset($row['name']) ? $row['name'] : '',
                                    'location' => isset($row['vicinity']) ? $row['vicinity'] : '',
                                    'position' => [
                                        'lat' => $lat,
                                        'lng' => $lng,
                                    ]

                                ];

                                $businessDataAppendArray[] = [
                                    'name' => isset($row['name']) ? $row['name'] : '',
                                    'rating' => isset($row['rating']) ? $row['rating'] : '',
                                    'image' => isset($url) ? $url : '',
                                    'address' => isset($row['vicinity']) ? $row['vicinity'] : '',
                                    'place_id' => isset($row['place_id']) ? $row['place_id'] : '',
                                ];
                                $appendFinalArray = [];
                                $appendFinalArray['locations'] = $graphDataAppendArray;
                                $appendFinalArray['business'] = $businessDataAppendArray;
                            }

                        }
                    }
                }
                return $this->helpReturn('Results are.', $appendFinalArray);
            }
        }
        catch(Exception $e)
        {
            Log::info("getMapCoordinates " . $e->getMessage());
            return $this->helpError(404, 'Some Problem happened to get result. please try again.');
        }
    }

    public function getPlaceSearch($lat, $lng,$keyword)
    {
        $coordinates = [$lat, $lng];
        $keywords = ['keyword' => $keyword,'name' => $keyword,'language' => 'pl','rankby' => 'prominence'];
        $mergecoordinates = implode(',', $coordinates);
        $radius = '16094';
        //'16094'
        if ($coordinates != '') {
            try {
                $results = [];
                $placeDetails = new PlacesApi('AIzaSyDIPbhytGCc5Oc6u41jD3n25AeVTfDXezM');
                $results = $placeDetails->nearbySearch($mergecoordinates,$radius,$keywords);
                return $this->helpReturn('Results are.', $results['results']);
            } catch (Exception $e) {
                Log::info(" getPlaceSearch " . $e->getMessage());
                return $this->helpError(404, $e->getMessage());
            }
        } else {
            Log::info('lat long missing');
            return $this->helpError(2, 'Lat Lng is missing');
        }

    }

    public function getBusinessListed($BusinessName)
    {
        $searchFor = $BusinessName;
        if ($searchFor != '')
        {
            try
            {
                $googlePlaces = new PlacesApi('AIzaSyDIPbhytGCc5Oc6u41jD3n25AeVTfDXezM');
                $results = $googlePlaces->textSearch($searchFor);
                $placeResultData['place_id'] = $results['results'][0]['place_id'];

                if(!empty($placeResultData['place_id']) ){

                    $placeResultData= $this->getBusinessListedPlaceResult($placeResultData['place_id']);
                }

                return $this->helpReturn('Results are.', $placeResultData);
            }
            catch (Exception $e)
            {
                Log::info(" getBusinessListed " . $e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        }
        else
        {
            return $this->helpError(2, 'Place string is missing.');
        }
    }

    public function getBusinessListedPlaceResult($PlaceId)
    {
        $placeFor = $PlaceId;

        if ($placeFor != '')
        {
            try
            {
                $googlePlaces = new PlacesApi('AIzaSyDIPbhytGCc5Oc6u41jD3n25AeVTfDXezM');
                $results = $googlePlaces->placeDetails($placeFor);

                $placeResultData['type'] = 'Google Places';
                $placeResultData['name'] = $results['result']['name'];
                $placeResultData['average_rating'] = $results['result']['rating'];
                $placeResultData['website'] = $results['result']['website'];
                $placeResultData['phone'] = $results['result']['international_phone_number'];
                $placeResultData['street'] = $results['result']['formatted_address'];
                $placeResultData['page_url'] = $results['result']['url'];
                $placeResultData['place_id'] = $results['result']['place_id'];
                $placeResultData['reviews'] = $results['result']['reviews'];
                $placeReviewData['reviewer'] = $results['result']['reviews'][0]['author_name'];
                $placeResultData['message'] = $results['result']['reviews'][0]['text'];
                $placeReviewData['review_url'] = $results['result']['reviews'][0]['author_url'];
                $placeReviewData['review_date'] = $results['result']['reviews'][0]['relative_time_description'];
                $uri_path = trim(parse_url($placeReviewData['review_url'], PHP_URL_PATH), '/');
                $uri_segments = explode('/', $uri_path);
                $placeReviewData['review_unique_identifier'] = $uri_segments[2];


                if (!empty($GooglePlaceResult['third_party_id']))
                {
                    $reviewData = [];
                    $i = 0;
                    $reviewData['third_party_id'] = $GooglePlaceResult['third_party_id'];
                    $reviewData['reviewer'] = $results['result']['reviews'][0]['author_name'];
                    $reviewData['message'] = $results['result']['reviews'][0]['text'];
                    $reviewData['rating'] = $results['result']['reviews'][0]['rating'];
                    $reviewData['review_url'] = $results['result']['reviews'][0]['author_url'];
                    $reviewData['review_date'] = $results['result']['reviews'][0]['relative_time_description'];
                    $uri_path = trim(parse_url($reviewData['review_url'], PHP_URL_PATH), '/');
                    $uri_segments = explode('/', $uri_path);
                    $reviewData['review_unique_identifier'] = $uri_segments[2];
                }

                return $this->helpReturn('Results are.', $placeResultData);
            }
            catch (Exception $e)
            {
                Log::info($e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        } else {
            return $this->helpError(2, 'Place Id is missing.');
        }
    }

    public function getBusinessUrlDetail(Request $request)
    {
        try
        {
            $businessUrl = $request->get('businessUrl');
            $client = new Client([]);

            $response = $client->request(
                'GET',
                'http://144.217.182.179:4548/sandbox/api/home/GetGooglePlaceBusinessDetailByURL',
                [
                    'query' => [
                        'BusinessURL' => $businessUrl,
                    ],
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            $records = $responseData['Results'];

            if($response->getStatusCode() == 200)
            {
                if( empty($records['Name']) )
                {
                    unset($records['URL']);
                }

                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Google Place Response", $records);
                }
            }

            return $this->helpError(404, 'Record not found.');
        }
        catch(Exception $e)
        {
            Log::info(" getBusinessUrlDetail " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getBusinessUrlHistoricalDetail($data)
    {
        try
        {
            $businessUrl = $data;
            $client = new Client([]);
            $appEnvironment = Config::get('apikeys.APP_ENV');

            $serverUrl = ( $appEnvironment == 'production') ? Config::get('custom.Scrapper_Prod_SERVER_URL'): Config::get('custom.SERVER_URL');
            $detailUrl = ( $appEnvironment == 'production') ? Config::get('custom.googleProdManualConnect'): Config::get('custom.googleTestManualConnect');

            $url = $serverUrl.$detailUrl;
            Log::info("url re $url");
            $response = $client->request(
                'GET',
                $url,
                [
                    'query' => [
                        'HistoricalReviews'=>'true',
                        'BusinessURL' => $businessUrl
                    ],
                ]
            );
            $responseData = json_decode($response->getBody()->getContents(), true);

           if($response->getStatusCode() == 200)
            {
                if( empty($responseData['Name']) )
                {
                    unset($responseData['URL']);
                }
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Google Place Historical Reviews", $responseData);
                }
            }

            return $this->helpError(404, 'Record not found.');

        }
        catch(Exception $e)
        {
            Log::info(" getBusinessUrlHistoricalDetail " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function storeThirdPartyMaster(Request $request)
    {
        $thirdPartyObj = new TripAdvisorEntity();
        $thirdPartyEntity = new ThirdPartyEntity();

        try
        {
            $googleEntity = new GooglePlaceEntity();
            // get business detail from google places.
            $result = $googleEntity->getFirstPlaceID($request);
            $responseCode = $result['_metadata']['outcomeCode'];
            /**
             * if place id get then go in,
             */
            if ($responseCode == 200) {

                $placeid = $result['records']['place_id'];
                $request->merge(array('placeid' => $placeid));
                $results = $googleEntity->getPlaceResult($request);
                $googleplaceresults = $results['records'];

                $fuzz = new Fuzz();

                if ($results['_metadata']['outcomeCode'] == 200)
                {
                    $userReviews = $results['records']['reviews'];

                    if ($googleplaceresults)
                    {
                        $score = $fuzz->tokenSortRatio($request->get('name'), $googleplaceresults['name']);
                        Log::info("Google API -> Score of -> $score > Business Name > " . $request->get('name') . " > GP AOI Name " . $googleplaceresults['name']);

                        if ($score < 40) {
                            $placeResultData = [];
                            $businessId = $request->get('business_id');
                            $placeResultData['business_id'] = $businessId;

                            $insertIssue = [
                                [
                                    'key' => 'name',
                                    'userID' => $request->get('userID'),
                                    'business_id' => $businessId,
                                    'issue' => 3,
                                    'type' => 'Google Places'
                                ]
                            ];

                            $thirdPartyObj->compareThirdPartyRecord($insertIssue);

                            return $this->helpReturn("Google Place go in not listed.");
                        }

                        $businessId = $request->get('business_id');
                        $placeResultData['type'] = 'Google Places';
                        $placeResultData['business_id'] = $businessId;
                        $placeResultData['name'] = $googleplaceresults['name'];
                        $placeResultData['average_rating'] = $googleplaceresults['average_rating'];
                        $placeResultData['website'] = $googleplaceresults['website'];
                        $placeResultData['phone'] = $googleplaceresults['phone'];
                        $placeResultData['street'] = $googleplaceresults['street'];
                        $placeResultData['page_url'] = $googleplaceresults['page_url'];

                        /**
                         * Remove http in website before saving into database
                         */

                        $str = $placeResultData['website'];
                        $str = preg_replace('#^http?://#', '', rtrim($str,'/'));

                        $placeResultData['website']=$str;
                        $GooglePlaceResult = GoogleplaceMaster::create($placeResultData);

                        $this->updateReviewCountAddReviewUrl($businessId);

                        $googleBusinessPageResult = TripadvisorMaster::where(
                            [
                                'business_id' => $businessId,
                                'type' => 'Google Places'
                            ]
                        )->first();

                        $totalBusinessReviews = 0;
                        if ( !empty($GooglePlaceResult['third_party_id']) )
                        {
                            // update $totalBusinessReviews with new value because we've update review_count value with
                            $totalBusinessReviews = $googleBusinessPageResult['review_count'];
                        }

                        $thirdPartyId = ( !empty($GooglePlaceResult['third_party_id'] ) ) ? $GooglePlaceResult['third_party_id'] : NULL;
                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $placeResultData['phone'],
                                'issue' => (filterPhoneNumber($placeResultData['phone']) != '') ? 7 : 46,
                                'oldIssue' => (filterPhoneNumber($placeResultData['phone']) == '') ? 7 : 46
                            ],
                            [
                                'key' => 'address',
                                'value' => $placeResultData['street'],
                                'issue' => ($placeResultData['street'] != '') ? 8 : 48,
                                'oldIssue' => ($placeResultData['street'] == '') ? 8 : 48
                            ],
                            [
                                'key' => 'website',
                                'value' => $placeResultData['website'],
                                'issue' => ($placeResultData['website'] != '') ? 9 : 47,
                                'oldIssue' => ($placeResultData['website'] == '') ? 9 : 47
                            ],
                            [
                                'key' => 'reviews',
                                'value' => $totalBusinessReviews,
                                'issue' => 67,
                                'oldIssue' => ""
                            ],
                            [
                                'key' => 'rating',
                                'value' => $placeResultData['average_rating'],
                                'issue' => 63,
                                'oldIssue' => ""
                            ]
                        ];

                        $thirdPartyEntity->globalIssueGenerator($request->get('userID'), $businessId, $thirdPartyId, $issueData, $placeResultData['type'], 'local');
                    }

                    return $this->helpReturn("Google Place Response.", $googleplaceresults);
                }
            }

            $placeResultData = [];
            $businessId = $request->get('business_id');
            $placeResultData['business_id'] = $businessId;

            $insertIssue = [
                [
                    'key' => 'name',
                    'userID' => $request->get('userID'),
                    'business_id' => $businessId,
                    'issue' => 3,
                    'type' => 'Google Places'
                ]
            ];

            $thirdPartyObj->compareThirdPartyRecord($insertIssue);
            return $this->helpReturn("Google Place go in not listed.");
        }
        catch (Exception $e)
        {
            Log::info("GP -> storeThirdPartyMaster >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * @param Request $request (business_id, userID, isNameChanged, businessKeyword (business Name)
     * is "isNameChanged" = true then we also update name & page_url by replace old one & also delete
     * previous reviews & add new reviews entries.
     *
     *
     * param Request2 $request (business_id, targetUrl, type)
     *
     * @return mixed
     */
    public function updateGooglePlacesMaster($request)
    {

        Log::info("updateGooglePlacesMaster " . json_encode($request->all()));
        $thirdPartyObj = new TripAdvisorEntity();
        $thirdPartyEntity = new ThirdPartyEntity();

        try
        {
            $userId = $request->get('userID');

            /**
             * Get business detail from google paces by search query.
             *
             * Only go in this block If user updating business from business form
             * not by   >>>> " Business URl"
             */

            if( empty( $request->get('targetUrl') ) )
            {
                $thirdPartyResult = ThirdPartyMaster::where(
                    [
                        'business_id' => $request->get('business_id'),
                        'type' => 'Google Places'
                    ]
                )->first();


                if($thirdPartyResult)
                {
                    /**
                     * This Business has been Manual deleted
                     * so we can not add this business again.
                     *
                     * is_manual_deleted = 1 represents that this business we deleted manually & not
                     * need to again create with same name.
                     *
                     * isNameChanged = empty(isNameChanged) make sure that business name not changed
                     * and still no need to do any activity here.
                     *
                     * We can only add this business by two ways
                     * 1- Manual Connect
                     * 2- By Replacing Business Name.
                     */
                    if( $thirdPartyResult['is_manual_deleted'] == 1 && empty($request->get('isNameChanged')) )
                    {
                          return $this->helpError(3, 'Business is already deleted.');
                    }

                    /**
                     * if data is manual connected & business name not changed
                     * then only re-compare issues with third party business record.
                     *
                     */
                    if( $thirdPartyResult['is_manual_connected'] == 1 && empty($request->get('isNameChanged'))
                        ||
                        (!empty($request->get('onlyIssuesCompare')) && empty($request->get('isNameChanged')))
                    )
                    {
                        Log::info("GP checking Issues");
                        $thirdPartyId = $thirdPartyResult['third_party_id'];

                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $thirdPartyResult['phone'],
                                'issue' => (filterPhoneNumber($thirdPartyResult['phone']) != '') ? 7 : 46,
                                'oldIssue' => (filterPhoneNumber($thirdPartyResult['phone']) == '') ? 7 : 46
                            ],
                            [
                                'key' => 'address',
                                'value' => $thirdPartyResult['street'],
                                'issue' => ($thirdPartyResult['street'] != '') ? 8 : 48,
                                'oldIssue' => ($thirdPartyResult['street'] == '') ? 8 : 48
                            ],
                            [
                                'key' => 'website',
                                'value' => $thirdPartyResult['website'],
                                'issue' => ($thirdPartyResult['website'] != '') ? 9 : 47,
                                'oldIssue' => ($thirdPartyResult['website'] == '') ? 9 : 47
                            ],
                            [
                                'key' => 'reviews',
                                'value' => $thirdPartyResult['review_count'],
                                'issue' => 67,
                                'oldIssue' => ""
                            ],
                            [
                                'key' => 'rating',
                                'value' => $thirdPartyResult['average_rating'],
                                'issue' => 63,
                                'oldIssue' => ""
                            ]
                        ];

                        $thirdPartyEntity->globalIssueGenerator($userId, $request->get('business_id'), $thirdPartyId, $issueData, 'Google Places', 'local');

                        return $this->helpReturn("Google Places Response.");
                    }
                }

                /*
                 * onlyIssuesCompare = true
                 * we don't want to again call scrapper. we've to only compare issues.
                 * But if third party data not found from table then return from here don't go next.
                 */
                if(!empty($request->get('onlyIssuesCompare')))
                {
                    return $this->helpError(3, 'Third party business compared.');
                }

                Log::info("GP going to call API");

                /**
                 * call api -> get place id
                 * yes it takes data every time, may be user has changed some
                 * info at google places.
                 */
                // get business detail from google places.
                $result = $this->getFirstPlaceID($request);
            } else {

                $arra = [
                    'businessUrl' => $request->get('targetUrl'),
                    'type' => $request->get('type')
                ];
                $request->merge($arra);
                // target to third party url detail/
                $result = $this->getBusinessUrlHistoricalDetail($request->get('targetUrl'));

            }
            return DB::transaction(function () use ($request, $result, $userId, $thirdPartyEntity, $thirdPartyObj) {
                $businessObj = new BusinessEntity();

                $responseCode = $result['_metadata']['outcomeCode'];

                $businessId = $request->get('business_id');

                $data['type'] = 'Google Places';
                $data['business_id'] = $businessId;

                $thirdPartyResult = TripadvisorMaster::where(
                    [
                        'business_id' => $businessId,
                        'type' => $data['type']
                    ]
                )->first();
                Log::info("Hell iam here");
                /**
                 * Place Id get
                 */
                if ($responseCode == 200)
                {

                    if ($result['_metadata']['outcomeCode'] == 200)
                    {
                        $records = $result['records'];
                        Log::info("Hell i got records");
                        /**
                         * if user business record exist on google places.
                         * update third_party_master table
                         */
                        if ($records)
                        {
                            /**
                             * Purpose of this check to restrict new Listing If previous business is already
                             * Inserted. If business gets updated Except Name but with any field Phone, street
                             *
                             * Previously Sometimes new business occur from scrapper if any field changed from
                             * business fields like phone. so to avoid this. put this check to stop business
                             * replace. Now When new business is occur and this is not matched with our existing
                             * third party Business we'll return from here.
                             */
                            if (empty($request->get('targetUrl'))) {
                                if (empty($request->get('isNameChanged')) && !empty($thirdPartyResult['name'])) {
                                    if (strtolower($thirdPartyResult['name']) != strtolower($records['name'])) {
                                        Log::info("GP check stop new listing" . $thirdPartyResult['name'] . " > " . $records['name']);

                                        return $this->helpReturn("Data already saved. New Business try to insert.", $thirdPartyResult);
                                    }
                                }
                                else
                                {
                                    Log::info("GP New business discovery process started");

                                    $fuzz = new Fuzz();

                                    $score = $fuzz->tokenSortRatio(strtolower($request->get('businessKeyword')), strtolower($records['name']));

                                    Log::info("Update GP -> Score of -> $score > Business Name > " . $request->get('businessKeyword') . " > GP API Name " . $records['name']);

                                    if ($score < 40) {
                                        Log::info("GP Accuracy failure");

                                        $thirdPartyMasterObj = new TripadvisorMaster();

                                        $insertIssue = [
                                            [
                                                'key' => 'name',
                                                'userID' => $request->get('userID'),
                                                'business_id' => $businessId,
                                                'issue' => 3,
                                                'type' => 'Google Places'
                                            ]
                                        ];

                                        $thirdPartyObj->compareThirdPartyRecord($insertIssue);

                                        /**
                                         * delete previous stored business trace from >> third_party-master
                                         *  first business was present & second time business not found on update
                                         * time, so delete the business.
                                         */
                                        $thirdPartyMasterObj->delThirdPartyBusiness($businessId, 'Google Places');

                                        return $this->helpError(404, 'Business accuracy failure.');
                                    }
                                }
                            }

                            Log::info("GP updating process started");


                            if( empty($request->get('targetUrl')) )
                            {
                                $data['average_rating'] = $records['average_rating'];
                                $data['website'] = $records['website'];
                                $data['phone'] = $records['phone'];
                                $data['street'] = $records['street'];

                                $data['city'] = !empty($records['city']) ? $records['city'] : '';
                                $data['country'] = !empty($records['country']) ? $records['country'] : '';
                                $data['zipcode'] = !empty($records['zipcode']) ? $records['zipcode'] : '';
                                $data['zipcode'] = !empty($records['zipcode']) ? $records['zipcode'] : '';

                                $userReviews = $result['records']['reviews'];
                            }
                            else
                            {
                                $records = $records['Results'];
                                $data['average_rating'] = $records['Rating'];
                                $data['review_count'] = $records['Review'];
                                $data['website'] = $records['Website'];
                                $data['phone'] = trim($records['ContactNo']);
                                $data['street'] = $records['AddressDetail']['Street'];
                                $data['city'] = $records['AddressDetail']['City'];
                                $data['zipcode'] = $records['AddressDetail']['Zip'];
                                $data['state'] = $records['AddressDetail']['State'];
                                $data['country'] = $records ['AddressDetail']['Country'];
                                $data['add_review_url'] = $records['AddReviewURL'];
                                $userReviews = $records['ReviewsDetail'];
                            }
                            /**
                             * Remove http in website before saving into database
                             */
                            $str = $data['website'];
                            $str = preg_replace('#^http?://#', '', rtrim($str,'/'));

                            $data['website'] = $str;
                            if( empty($request->get('targetUrl')) )
                            {
                                $isNameChanged = $request->get('isNameChanged');
                                $data['is_manual_connected'] = 0;
                            }
                            else if( !empty($request->get('targetUrl')) )
                            {
                                /**
                                 * check if business name changed and we search from
                                 * url by manual connect
                                 */
                                if ( !empty($thirdPartyResult['third_party_id']) ){
                                    Log::info("Hell here i am");
                                    $oldBusinessName = strtolower($thirdPartyResult['name']);
                                    $newBusinessName = strtolower($records['Name']);
                                    $isNameChanged = ($newBusinessName != $oldBusinessName) ? true : false;
                                    $data['is_manual_connected'] = 1;
                                }
                            }

                            /**
                             * isNameChanged = true
                             * Delete previously stored reviews
                             * Store new reviews in system.
                             */
                            if (!empty($thirdPartyResult)){
                                Log::info("Hell here i am 2");
                                if ( empty($thirdPartyResult['third_party_id']) )
                                {
                                    $isNameChanged = true;
                                }
                                else
                                {
                                    if($thirdPartyResult['name'] == '')
                                    {
                                        $isNameChanged = true;
                                    }
                                }
                            }else{
                                Log::info("Hell here i am 3");
                                $isNameChanged = true;
                            }


                            // name will be replaced with the new one.
                            if ($isNameChanged)
                            {
                                $data['name'] = empty($request->get('targetUrl')) ? $records['name'] : $records['Name'];
                                $data['page_url'] = empty($request->get('targetUrl')) ? $records['page_url'] : $records['URL'];
                            }

                            $data['is_manual_deleted'] = 0;

                            if(empty($data['average_rating']))
                            {
                                $data['average_rating'] = null;
                            }

                            if(empty($data['review_count']))
                            {
                                $data['review_count'] = null;
                            }

                            if ( !empty($thirdPartyResult['third_party_id']) )
                            {
                                $thirdPartyId = $thirdPartyResult['third_party_id'];
                                $thirdPartyResult->update($data);
                            }
                            else
                            {
                                $thirdPartyResult = TripadvisorMaster::create($data);
                                $thirdPartyId = ( !empty($thirdPartyResult['third_party_id']) ) ? $thirdPartyResult['third_party_id'] : NULL;
                            }

                            // update review_cont by manual connect value because we didn't get review count from Google API.
                            $this->updateReviewCountAddReviewUrl($businessId);

                            $totalBusinessReviews = $thirdPartyResult['review_count'];

                            Log::info("First review " . $totalBusinessReviews);

                            if( empty($request->get('targetUrl')) )
                            {
                                $googlePlaceResult = TripadvisorMaster::where(
                                    [
                                        'business_id' => $businessId,
                                        'type' => $data['type']
                                    ]
                                )->first();

                                if ( !empty($thirdPartyResult['third_party_id']) )
                                {
                                    // update $totalBusinessReviews with new value because we've update review_count value with

                                    $totalBusinessReviews = $googlePlaceResult['review_count'];
                                }
                            }

                            Log::info("modified reviw " . $totalBusinessReviews);

                            $issueData = [
                                [
                                    'key' => 'phone',
                                    'value' => $thirdPartyResult['phone'],
                                    'issue' => (filterPhoneNumber($thirdPartyResult['phone']) != '') ? 7 : 46,
                                    'oldIssue' => (filterPhoneNumber($thirdPartyResult['phone']) == '') ? 7 : 46
                                ],
                                [
                                    'key' => 'address',
                                    'value' => $thirdPartyResult['street'],
                                    'issue' => ($thirdPartyResult['street'] != '') ? 8 : 48,
                                    'oldIssue' => ($thirdPartyResult['street'] == '') ? 8 : 48
                                ],
                                [
                                    'key' => 'website',
                                    'value' => $thirdPartyResult['website'],
                                    'issue' => ($thirdPartyResult['website'] != '') ? 9 : 47,
                                    'oldIssue' => ($thirdPartyResult['website'] == '') ? 9 : 47
                                ],
                                [
                                    'key' => 'reviews',
                                    'value' => $totalBusinessReviews,
                                    'issue' => 67,
                                    'oldIssue' => ""
                                ],
                                [
                                    'key' => 'rating',
                                    'value' => $thirdPartyResult['average_rating'],
                                    'issue' => 63,
                                    'oldIssue' => ""
                                ]
                            ];

                            $thirdPartyEntity->globalIssueGenerator($userId, $businessId, $thirdPartyId, $issueData, $data['type'], 'local');

                            /**
                             * If business name replaced with the new one.
                             * Delete previously stored reviews
                             * Store new reviews in system.
                             */
                            if( $thirdPartyId && $isNameChanged )
                            {
                                TripadvisorReview::where('third_party_id', $thirdPartyId)->delete();
                                StatTracking::where('third_party_id', $thirdPartyId)->delete();

                                /**
                                 * Only save reviews in system
                                 * if business is updated VIA Manual connect
                                 */
                                if( !empty($userReviews) && !empty($request->get('targetUrl')))
                                {
                                    $thirdPartyObj->storeUserReviews($userReviews, $thirdPartyId, $data['type'], $request);
                                }
                            }

                            return $this->helpReturn("Google Places Response.", $thirdPartyResult);
                        }
                    }
                    else if($result['_metadata']['outcomeCode'] == 404)
                    {
                        // result not get
                        $responseCode = $result['_metadata']['outcomeCode'];
                    }
                }

                if( $responseCode == 404 && empty( $request->get('targetUrl') ) ) {


                    /**
                     * User does not change the Business Name but If we'll not get any Business on change Phone or field
                     * if any chance occured then we'll not delete user existing business because user does not want
                     * to delete that business
                     */
                    if (empty($request->get('isNameChanged')) && !empty($thirdPartyResult['name'])) {
                        Log::info("no need to delete a business record and generate an issue");
                        return $this->helpError(404, 'Business record not found.');
                    }


                    /**
                     * we make sure that this condition only worked if we
                     * are using this method from Business form not by manual connect/
                     * because manual connect has not to again compare/delete.
                     *
                     */
                    $businessId = $request->get('business_id');

                    $thirdPartyMasterObj = new TripadvisorMaster();

                    $insertIssue = [
                        [
                            'key' => 'name',
                            'userID' => $request->get('userID'),
                            'business_id' => $businessId,
                            'issue' => 3,
                            'type' => 'Google Places'
                        ]
                    ];

                    $thirdPartyObj->compareThirdPartyRecord($insertIssue);

                    /**
                     * delete previous stored business trace from >> third_party-master
                     *  first business was present & second time business not found on update
                     * time, so delete the business.
                     */
                    $thirdPartyMasterObj->delThirdPartyBusiness($businessId, 'Google Places');
                }

                return $this->helpError(404, 'Business record not Found..');
            });

        }
        catch (Exception $e)
        {
            Log::info(" updateGooglePlacesMaster > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function updateReviewCountAddReviewUrl($business_id)
    {
        try {
            $googleEntity = new GooglePlaceEntity();
            $googlePlaceData = TripadvisorMaster::select('page_url', 'business_id', 'type')
                ->where('type', 'google places')
                ->where('business_id', $business_id)->first();

            if (strtolower($googlePlaceData['type']) == 'google places')
            {
                $result = $googleEntity->getBusinessUrlHistoricalDetail($googlePlaceData['page_url']);

                if ($result['_metadata']['outcomeCode'] == 200)
                {
                    $review = $result['records']['Results']['Review'];
                    $AddReviewUrl = $result['records']['Results']['AddReviewURL'];

                    TripadvisorMaster::where('business_id', $business_id)
                        ->where('type', $googlePlaceData['type'])
                        ->update(['add_review_url' => $AddReviewUrl, 'review_count' =>$review ]);
                }
            }
            return $this->helpReturn("Google Place Review Count and Add Review Url Updated.");
        }
        catch (Exception $e)
        {
            Log::info(" updateReviewCountAddReviewUrl " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function notifyUsers($request)
    {
        try{
            $businessResult = DB::table('business_master as bm')
                ->join('third_party_master as gpm', 'bm.business_id', '=', 'gpm.business_id')
                ->join('user_master as usm', 'bm.user_id', '=', 'usm.id')
                ->select('bm.user_id', 'usm.first_name', 'bm.business_id', 'gpm.business_id as googlePlaceBusinessId', 'bm.name', 'third_party_id', 'page_url')
                ->where('gpm.type', 'Google Places')
                ->where('bm.business_profile_status', 'completed')
                ->get();

            if(!empty($businessResult))
            {
                $googleEntity = new GooglePlaceEntity();

                foreach($businessResult as $row) {

                    $arra = ['name' => $row->name];
                    $request->merge($arra);

                    // get place id from google place
                    $result = $googleEntity->getFirstPlaceID($request);

                    if ($result['_metadata']['outcomeCode'] == 200) {

                        $placeid = $result['records']['place_id'];
                        //   return $placeid;
                        $request->merge(array('placeid' => $placeid));
                        $results = $googleEntity->getPlaceResult($request);

                        if ($results['_metadata']['outcomeCode'] == 200) {

                            $googleplaceresults = $results['records'];
                            $userReviews = $results['records']['reviews'];

                            /**
                             * if user business record meet on google place area
                             * update third_party_master table
                             */
                            if ($googleplaceresults) {
                                /**
                                 * if user has reviews of current business then also update user Review
                                 * against in third_party_review table..
                                 */
                                if ((!empty($userReviews))) {

                                    //    $result = $this->storeUserReviews($userReviews, $row->third_party_id);

                                    /**
                                     * send notify to user, If any new entry posted
                                     */
                                    if ($result != 0) {

                                        // get googleplace message.
                                        $chatMasterResult = ChatMaster::select('message')->find(10);

                                        $message = $chatMasterResult['message'] . ' >>' . $row->page_url;
                                        $message = reformatText($message, $row->first_name);

                                        $chatRequest = [
                                            'message' => $message,
                                            'user_id' => $row->user_id,
                                            'action' => 'reply_awaiting',
                                            'unread' => 1,
                                        ];

                                        $request->merge($chatRequest);

                                        // madison send notification to user.
                                        $chatResult = $this->ChatHistoryEntity->systemSendNotification($request);

                                        if ($chatResult['_metadata']['outcomeCode'] == 200) {
                                            // make logs of current chat id against specific issue.

                                            $chatid = $chatResult['records']['chat_id'];


                                            /**
                                             * 10 is linked with googleplace if user click googleplace then
                                             *we take this id to get our result.
                                             */
                                            $data = [
                                                'chat_id' => $chatid,
                                                'issue_id' => 10,
                                            ];

                                            ChatIssueLogs::create($data);

                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return $this->helpReturn("Process Complete.");
            }
        } catch (Exception $e) {
            Log::info("notifyUsers > Google Place Entity >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getBusinessReviews($request)
    {
        $searchFor = $request->get('name');

        if ($searchFor != '') {
            try {
                $googlePlaces = new PlacesApi('AIzaSyDIPbhytGCc5Oc6u41jD3n25AeVTfDXezM');
                $results = $googlePlaces->textSearch($searchFor);

                $placeResultData['place_id'] = $results['results'][0]['place_id'];

                $googlePlaceResultDetail = $this->getPlaceResult($placeResultData['place_id']);

                return $googlePlaceResultDetail;

            } catch (Exception $e) {
                Log::info(" getBusinessReviews -> " . $e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        } else {
            return $this->helpError(2, 'Business Location is missing.');
        }
    }

}
