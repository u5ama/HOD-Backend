<?php

namespace Modules\ThirdParty\Entities;

use App\Entities\AbstractEntity;
use App\Traits\GlobalResponseTrait;
use App\Traits\UserAccess;
use Exception;
use FuzzyWuzzy\Fuzz;
use Log;
use Config;
use DB;
use JWTAuth;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Models\Business;
//use Modules\Business\Models\ChatMaster;

use Modules\ThirdParty\Models\StatTracking;

use Modules\ThirdParty\Entities\DashboardEntity;

use Modules\ThirdParty\Entities\GooglePlaceEntity;
use Modules\ThirdParty\Models\IssuesList;

//use Modules\MadisonCentral\Entities\ChatHistoryEntity;
//use Modules\MadisonCentral\Models\ChatHistory;
//use Modules\MadisonCentral\Models\ChatIssueLogs;

use Modules\ThirdParty\Models\UserIssues;

use Modules\ThirdParty\Models\TripadvisorMaster;

use GuzzleHttp\Client;

use Modules\ThirdParty\Models\TripadvisorReview;

//use Davibennun\LaravelPushNotification\PushNotification;

use Modules\ThirdParty\Entities\YelpEntity;

class TripAdvisorEntity extends AbstractEntity
{
    use UserAccess;

    public function __construct()
    {
    }

    public function getTripadvisorPage($request)
    {
        try {
            $tripadvisorlist = TripadvisorMaster::where('type', 'Trip Advisor')->pluck('page_url');
            if ($tripadvisorlist) {
                return $this->helpReturn("Page Url.", $tripadvisorlist);
            } else {
                return $this->helpError(404, ' No Data exists');
            }
        } catch (Exception $e) {
            Log::info("tripadvisorentity > getTripadvisorPage >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getGoogleplacePage($request)
    {
        try {
            $tripadvisorlist = TripadvisorMaster::where('type', 'Google Places')->pluck('page_url');
            if ($tripadvisorlist) {
                return $this->helpReturn("Page Url.", $tripadvisorlist);
            } else {
                return $this->helpError(404, ' No Data exists');
            }
        } catch (Exception $e) {
            Log::info("tripadvisorentity > getGoogleplacePage >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * Get data from tripadvisor of given business name.
     * @param Request $request
     * @return mixed
     */
    public function getBusinessDetail(Request $request)
    {
        try {
            if ($request->has('businessKeyword')) {
                $businessKeyword = $request->get('businessKeyword');
            } elseif ($request->has('name')) {
                $businessKeyword = $request->get('name');
            }

            $query = ['Keyword' => $businessKeyword];

            if ($request->has('phone')) {
                $query['PhoneNo'] = $request->get('phone');
            }

            Log::info("tripAdvisor query " . json_encode($query));

            $appEnvironment = Config::get('apikeys.APP_ENV');

            $serverUrl = ( $appEnvironment == 'production') ? Config::get('custom.Scrapper_Prod_SERVER_URL'): Config::get('custom.SERVER_URL');
            $detailUrl = ($appEnvironment == 'production') ? Config::get('custom.tripAdvisorProdBusinessDetail') : Config::get('custom.tripAdvisorTestBusinessDetail');

            $url = $serverUrl . $detailUrl;

            $client = new Client([]);
            $response = $client->request(
                'GET', $url,
                [
                    'query' => $query
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);
            $records = $responseData['Results'];

            if ($response->getStatusCode() == 200) {
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Trip Advisor Response.", $responseData);
                }
            }
            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info("tripadvisorentity > getBusinessDetail >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getBusinessListed($businessName)
    {
        try {

            Log::info("Business Listed TA Cron Job > $businessName");
            $client = new Client([]);
            $response = $client->request(
                'GET',
                'http://67.227.145.153:4548/sandbox/api/home/GetBuisnessDetail',
                [
                    'query' => [
                        'keyword' => $businessName,
                    ],
                ]
            );
            $responseData = json_decode($response->getBody()->getContents(), true);
            Log::info("TA STORE complete");
            $records = $responseData['Results'];
            if ($response->getStatusCode() == 200) {
                Log::info("TA STORE complete in");
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Trip Advisor Response.", $responseData);
                }
                return $this->helpError(404, 'Record not found.');
            }
        } catch (Exception $e) {
            Log::info("tripadvisorentity > getBusinessListed >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * Get page_url from third_party_master table and
     * save reviews in third_party_review table
     * @param $request (token, type, (Tripadvisor,Yelp,Google Places, all)
     * @return mixed
     */
    public function SaveHistoricalReviews(Request $request)
    {
        Log::info("special saving call");
        try {
            $tripEntity = new TripAdvisorEntity();
            $googleEntity = new GooglePlaceEntity();
            $businessObj = new BusinessEntity();

            $businessResult = $businessObj->userSelectedBusiness($request);
            if($businessResult['_metadata']['outcomeCode'] != 200)
            {
                return $businessResult;
            }

            $type = !empty(($request->get('type'))) ? $request->get('type') : 'all';

            $data = TripadvisorMaster::select('page_url', 'third_party_id', 'type')
                ->where('business_id', $businessResult['records']['business_id'])
                ->where('page_url', '!=', '')
                ->where(function ($q) use ($type) {
                    if ($type != 'all') {
                        $q->where('type', $type);
                    }
                })
                ->get()->toArray();
            if (empty($data)) {
                return $this->helpError(404, 'Data not found of requested type.');
            }

            if ($type != '' && $type != 'all') {

                foreach ($data as $row) {
                    $pageUrl = $row['page_url'];
                    $type = $row['type'];
                    $thirdPartyId = $row['third_party_id'];

                    if (strtolower($type) == 'google places') {
                        $result = $googleEntity->getBusinessUrlHistoricalDetail($pageUrl);
                    }
                    if ($result['_metadata']['outcomeCode'] == 200) {
                        $job = '';
                        $this->storeUserReviews($result['records']['Results']['ReviewsDetail'], $thirdPartyId, $type, $request, $job);
                    }

                    return $this->helpReturn("Reviews Saved.");
                }
            }
            else {
                foreach ($data as $row) {
                    Log::info("special tpe " . $row['type']);

                    $pageUrl = $row['page_url'];
                    $thirdPartyType = $row['type'];
                    $thirdPartyId = $row['third_party_id'];

                    if (strtolower($thirdPartyType) == 'google places') {
                        $result = $googleEntity->getBusinessUrlHistoricalDetail($pageUrl);
                    }

                    // all
                    if ($result['_metadata']['outcomeCode'] == 200) {
                        $this->storeUserReviews($result['records']['Results']['ReviewsDetail'], $thirdPartyId, $thirdPartyType, $request);
                    }
                }

                return $this->helpReturn("Reviews Saved.");
            }
            return $this->helpReturn("Request Processed.");
        } catch (Exception $e) {
            Log::info("tripadvisorentity > SaveHistoricalReviews >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getBusinessHistoricalDetail(Request $request)
    {
        try {
            $businessKeyword = $request->get('businessKeyword');
            $client = new Client([]);
            $response = $client->request(
                'GET',
                'http://67.227.145.153:4548/sandbox/api/home/GetBuisnessDetail',
                [
                    'query' => [
                        'HistoricalReviews' => 'true',
                        'Keyword' => $businessKeyword,
                    ],
                ]
            );
            $responseData = json_decode($response->getBody()->getContents(), true);
            $records = $responseData['Results'];
            if ($response->getStatusCode() == 200) {
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Trip Advisor Historical Business Detail.", $responseData);
                }
                return $this->helpError(404, 'Record not found.');
            }
        } catch (Exception $e) {
            Log::info("tripadvisorentity > getBusinessHistoricalDetail >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * Get data from tripadvisor of given business url.
     * @param Request $request
     * @return mixed
     */
    public function getBusinessUrlDetail(Request $request)
    {
        try {
            $businessUrl = $request->get('businessUrl');
            $client = new Client([]);
            $response = $client->request(
                'GET',
                'http://144.217.182.179:4548/sandbox/api/home/GetBuisnessDetailByURL',
                [
                    'query' => [
                        'BuisnessURL' => $businessUrl,
                    ],
                ]
            );
            $responseData = json_decode($response->getBody()->getContents(), true);
            if ($response->getStatusCode() == 200) {
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Trip Advisor Response.", $responseData);
                }
            }
            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info(" Trip Advisor getBusinessUrlDetail >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getBusinessUrlHistoricalDetail($pageUrl)
    {
        Log::info("TA start url > $pageUrl");
        try {
            $appEnvironment = Config::get('apikeys.APP_ENV');

            $serverUrl = ( $appEnvironment == 'production') ? Config::get('custom.Scrapper_Prod_SERVER_URL'): Config::get('custom.SERVER_URL');
            $detailUrl = ( $appEnvironment == 'production') ? Config::get('custom.tripAdvisorProdManualConnect'): Config::get('custom.tripAdvisorTestManualConnect');

            $url = $serverUrl.$detailUrl;

            Log::info("ur;");
            Log::info($url);


            $client = new Client([]);
            $response = $client->request(
                'GET',
                $url,
                [
                    'query' => [
                        'HistoricalReviews' => 'true',
                        'BuisnessURL' => $pageUrl,
                    ],
                ]
            );
            $responseData = json_decode($response->getBody()->getContents(), true);
            Log::info("TA complete");
            if ($response->getStatusCode() == 200) {

                Log::info("complete in");
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Trip Advisor Historical Business Url Detail.", $responseData);
                }
            }
            return $this->helpError(404, 'Record not found.');
            Log::info('Record not found');
        } catch (Exception $e) {
            Log::info(" Trip Advisor getBusinessUrlHistoricalDetail >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     *
     * Save data of tripadvisor in our system.
     * @param Request $request (business_id,businessKeyword, userId)
     * @return mixed
     */
    public function storeThirdPartyMaster(Request $request)
    {
        Log::info("Business Tripadvisor Register Process started" . json_encode($request->all()));
        try {
            $tripEntity = new TripAdvisorEntity();
            $thirdPartyEntity = new ThirdPartyEntity();

            // get business detail from trip advisor.
            $result = $tripEntity->getBusinessDetail($request);

            $responseCode = $result['_metadata']['outcomeCode'];
            //  $data = [];
            $data['type'] = 'Tripadvisor';

            if ($responseCode == 200) {
                $records = $result['records']['Results'];
                $userReviews = $result['records']['Results']['ReviewsDetail'];

                $fuzz = new Fuzz();

                if ($records) {
                    $score = $fuzz->tokenSortRatio($request->get('name'), $records['Name']);
                    Log::info("TA Scrapper -> Score of -> $score > Business Name > " . $request->get('name') . " > TA Scrapper Name " . $records['Name']);

                    if ($score >= 40) {
                        Log::info("Ok for TA sc");

                        $businessId = $request->get('business_id');
                        $data['business_id'] = $businessId;
                        $data['name'] = $records['Name'];
                        $data['page_url'] = $records['URL'];
                        $data['review_count'] = $records['Review'];
                        $data['average_rating'] = $records['Rating'];
                        $data['phone'] = $records['ContactNo'];
                        $data['street'] = $records['AddressDetail']['Street'];
                        $data['city'] = $records['AddressDetail']['City'];
                        $data['zipcode'] = $records['AddressDetail']['Zip'];
                        $data['state'] = $records['AddressDetail']['State'];
                        $data['country'] = $records ['AddressDetail']['Country'];
                        $data['website'] = $records['Website'];
                        $data['add_review_url'] = $records['AddReviewURL'];
                        /**
                         * Remove http in website before saving into database
                         */
                        $str = $data['website'];
                        $str = preg_replace('#^http?://#', '', rtrim($str, '/'));
                        $data['website'] = $str;
                        $TripadvisorResult = TripadvisorMaster::create($data);
                        $thirdPartyId = (!empty($TripadvisorResult['third_party_id'])) ? $TripadvisorResult['third_party_id'] : NULL;
                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $data['phone'],
                                'issue' => (filterPhoneNumber($data['phone']) != '') ? 4 : 40,
                                'oldIssue' => (filterPhoneNumber($data['phone']) == '') ? 4 : 40
                            ],
                            [
                                'key' => 'address',
                                'value' => $data['street'],
                                'issue' => ($data['street'] != '') ? 5 : 42,
                                'oldIssue' => ($data['street'] == '') ? 5 : 42
                            ],
                            [
                                'key' => 'website',
                                'value' => $data['website'],
                                'issue' => ($data['website'] != '') ? 6 : 41,
                                'oldIssue' => ($data['website'] == '') ? 6 : 41
                            ],
                            [
                                'key' => 'reviews',
                                'value' => $data['review_count'],
                                'issue' => 65,
                                'oldIssue' => ""
                            ],
                            [
                                'key' => 'rating',
                                'value' => $data['average_rating'],
                                'issue' => 61,
                                'oldIssue' => ""
                            ]
                        ];
                        $thirdPartyEntity->globalIssueGenerator($request->get('userID'), $businessId, $thirdPartyId, $issueData, $data['type'], 'local');
                        // here is the new check come for new issues
                        return $this->helpReturn("Trip Advisor Response.", $TripadvisorResult);
                    } else {
                        Log::info("Name accuracy issue");
                        $responseCode = 404;
                    }
                }
            }

            if ($responseCode == 404 || $responseCode == 1) {
                $businessId = $request->get('business_id');
                $data['business_id'] = $businessId;
                $insertIssue = [
                    [
                        'key' => 'name',
                        'userID' => $request->get('userID'),
                        'business_id' => $businessId,
                        'issue' => 2,
                        'type' => $data['type']
                    ]
                ];
                $this->compareThirdPartyRecord($insertIssue);
            }

            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info("tripadvisorentity > storeThirdPartyMaster >> " . $e->getMessage());
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
    public function updateThirdPartyMaster(Request $request)
    {
        Log::info("Business Update -> tripadvisor Process started " . json_encode($request->all()));

        $thirdPartyObj = new TripAdvisorEntity();
        $thirdPartyEntity = new ThirdPartyEntity();
        $userId = $request->get('userID');

        try {
            /**
             * Get business detail from tripadvisor by search query.
             *
             * Only go in this block If user updating business from business form
             * not by   >>>> " Business URl"
             */
            if (empty($request->get('targetUrl'))) {

                $tripAdvisorResult = TripadvisorMaster::where(
                    [
                        'business_id' => $request->get('business_id'),
                        'type' => 'Tripadvisor'
                    ]
                )->first();

                if ($tripAdvisorResult) {

                    /**
                     * This Business has been Manual deleted
                     * so we can not add this business again.
                     * We can only add this business by two ways
                     * 1- Manual Connect
                     * 2- By Replacing Business Name.
                     */
                    if ($tripAdvisorResult['is_manual_deleted'] == 1 && empty($request->get('isNameChanged'))) {
                        return $this->helpError(3, 'Business stats showing this business already deleted. so you can only connect it By Manual connect or replace Business Name.');
                    }

                    /**
                     * if data is manual connected & business name not changed
                     * then only re-compare issues with third party business record.
                     */
                    if (($tripAdvisorResult['is_manual_connected'] == 1 && empty($request->get('isNameChanged')))
                        ||
                        (!empty($request->get('onlyIssuesCompare')) && empty($request->get('isNameChanged')))
                    ) {
                        $thirdPartyId = $tripAdvisorResult['third_party_id'];
                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $tripAdvisorResult['phone'],
                                'issue' => (filterPhoneNumber($tripAdvisorResult['phone']) != '') ? 4 : 40,
                                'oldIssue' => (filterPhoneNumber($tripAdvisorResult['phone']) == '') ? 4 : 40
                            ],
                            [
                                'key' => 'address',
                                'value' => $tripAdvisorResult['street'],
                                'issue' => ($tripAdvisorResult['street'] != '') ? 5 : 42,
                                'oldIssue' => ($tripAdvisorResult['street'] == '') ? 5 : 42
                            ],
                            [
                                'key' => 'website',
                                'value' => $tripAdvisorResult['website'],
                                'issue' => ($tripAdvisorResult['website'] != '') ? 6 : 41,
                                'oldIssue' => ($tripAdvisorResult['website'] == '') ? 6 : 41
                            ],
                            [
                                'key' => 'reviews',
                                'value' => $tripAdvisorResult['review_count'],
                                'issue' => 65,
                                'oldIssue' => ""
                            ],
                            [
                                'key' => 'rating',
                                'value' => $tripAdvisorResult['average_rating'],
                                'issue' => 61,
                                'oldIssue' => ""
                            ]
                        ];
                        $thirdPartyEntity->globalIssueGenerator($userId, $request->get('business_id'), $thirdPartyId, $issueData, 'Tripadvisor', 'local');
                        return $this->helpReturn("Trip Advisor Response.");
                    }
                }

                /*
                 * onlyIssuesCompare = true
                 * we don't want to again call scrapper. we've to only compare issues.
                 * But if third party data not found from table then return from here don't go next.
                 */
                if (!empty($request->get('onlyIssuesCompare'))) {
                    return $this->helpError(3, 'Third party business compared.');
                }

                $data['type'] = 'Tripadvisor';

                /**
                 * call scrapper api -> get business detail from tripadvisor
                 * yes it takes data every time, may be user has changed some
                 * info at tripadvisor.
                 */
                $result = $this->getBusinessDetail($request);
                $responseCode = $result['_metadata']['outcomeCode'];

                Log::info("TA result " . json_encode($result));

                if ($responseCode == 200) {
                    $records = $result['records']['Results'];
                    Log::info('tripadvisor ka scrapper name' . $records['Name']);
                    Log::info('(ta) ka business name' . $request->businessKeyword);
                }
            }
            else {
                // target to third party url detail/
                $result = $this->getBusinessUrlHistoricalDetail($request->get('targetUrl'));
            }

            return DB::transaction(function () use ($request, $result, $userId, $thirdPartyEntity) {
                $businessObj = new BusinessEntity();
                $businessId = $request->get('business_id');

                // request response
                $responseCode = $result['_metadata']['outcomeCode'];

                $data = [];
                $data['type'] = 'Tripadvisor';

                $tripAdvisorResult = TripadvisorMaster::where(
                    [
                        'business_id' => $businessId,
                        'type' => $data['type']
                    ]
                )->first();

                if ($responseCode == 200) {
                    $records = $result['records']['Results'];
                    $userReviews = $result['records']['Results']['ReviewsDetail'];

                    /**
                     * if user business record meet on trip advisor area
                     * update trip_advisor_master_table
                     */
                    if ($records) {
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
                            if (empty($request->get('isNameChanged')) && !empty($tripAdvisorResult['name'])) {
                                if (strtolower($tripAdvisorResult['name']) != strtolower($records['Name'])) {
                                    Log::info("TA check stop new listing" . $tripAdvisorResult['name'] . " > " . $records['Name']);

                                    return $this->helpReturn("Data already saved. New Business try to insert.", $tripAdvisorResult);
                                }
                            }
                            else
                            {
                                Log::info("TA New business discovery process started");

                                $fuzz = new Fuzz();

                                $score = $fuzz->tokenSortRatio(strtolower($request->get('businessKeyword')), strtolower($records['Name']));

                                Log::info("Update TA Scrapper -> Score of -> $score > Business Name > " . $request->get('businessKeyword') . " > TA Scrapper Name " . $records['Name']);

                                if ($score < 40) {
                                    Log::info("TA Accuracy failure");

                                    $thirdPartyObj = new TripadvisorMaster();
                                    $insertIssue = [
                                        [
                                            'key' => 'name',
                                            'userID' => $userId,
                                            'business_id' => $businessId,
                                            'issue' => 2,
                                            'type' => $data['type']
                                        ]
                                    ];
                                    $this->compareThirdPartyRecord($insertIssue);
                                    /**
                                     * delete previous stored business trace from >> third_party-master
                                     *  first business was present & second time business not found on update
                                     * time, so delete the business.
                                     */
                                    $thirdPartyObj->delThirdPartyBusiness($businessId, $data['type']);

                                    return $this->helpError(404, 'Business accuracy failure.');
                                }
                            }
                        }

                        Log::info("TA updating process started");


                        $data['type'] = 'Tripadvisor';
                        $data['business_id'] = $businessId;
                        $data['review_count'] = $records['Review'];
                        $data['average_rating'] = $records['Rating'];
                        $data['phone'] = $records['ContactNo'];
                        $data['street'] = $records['AddressDetail']['Street'];
                        $data['city'] = $records['AddressDetail']['City'];
                        $data['zipcode'] = $records['AddressDetail']['Zip'];
                        $data['state'] = $records['AddressDetail']['State'];
                        $data['country'] = $records ['AddressDetail']['Country'];
                        $data['website'] = $records['Website'];
                        $data['add_review_url'] = $records['AddReviewURL'];
                        /**
                         * Remove http in website before saving into database
                         */
                        $str = $data['website'];
                        $str = preg_replace('#^http?://#', '', rtrim($str, '/'));
                        $data['website'] = $str;

                        if (empty($request->get('targetUrl'))) {
                            $isNameChanged = $request->get('isNameChanged');
                            $data['is_manual_connected'] = 0;
                        } else if (!empty($request->get('targetUrl'))) {
                            /**
                             * check if business name changed and we search from
                             * url by manual connect
                             */
                            $oldBusinessName = strtolower($tripAdvisorResult['name']);
                            // trip advisor name
                            $newBusinessName = strtolower($records['Name']);
                            $isNameChanged = ($newBusinessName != $oldBusinessName) ? true : false;
                            $data['is_manual_connected'] = 1;
                        }

                        /**
                         * isNameChanged = true
                         * Delete previously stored reviews
                         * Store new reviews in system.
                         */
                        if (empty($tripAdvisorResult['third_party_id']) || $tripAdvisorResult['name'] == '') {

                            $isNameChanged = true;
                        }

                        // name will be replaced with the new one.
                        if ($isNameChanged) {
                            $data['name'] = $records['Name'];
                            $data['page_url'] = $records['URL'];
                        }

                        $data['is_manual_deleted'] = 0;

                        if (!empty($tripAdvisorResult['third_party_id'])) {
                            $thirdPartyId = $tripAdvisorResult['third_party_id'];
                            $tripAdvisorResult->update($data);
                        } else {
                            $tripAdvisorResult = TripadvisorMaster::create($data);
                            $thirdPartyId = (!empty($tripAdvisorResult['third_party_id'])) ? $tripAdvisorResult['third_party_id'] : NULL;
                        }

                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $data['phone'],
                                'issue' => (filterPhoneNumber($data['phone']) != '') ? 4 : 40,
                                'oldIssue' => (filterPhoneNumber($data['phone']) == '') ? 4 : 40
                            ],
                            [
                                'key' => 'address',
                                'value' => $data['street'],
                                'issue' => ($data['street'] != '') ? 5 : 42,
                                'oldIssue' => ($data['street'] == '') ? 5 : 42
                            ],
                            [
                                'key' => 'website',
                                'value' => $data['website'],
                                'issue' => ($data['website'] != '') ? 6 : 41,
                                'oldIssue' => ($data['website'] == '') ? 6 : 41
                            ],
                            [
                                'key' => 'reviews',
                                'value' => $data['review_count'],
                                'issue' => 65,
                                'oldIssue' => ""
                            ],
                            [
                                'key' => 'rating',
                                'value' => $data['average_rating'],
                                'issue' => 61,
                                'oldIssue' => ""
                            ]
                        ];
                        $thirdPartyEntity->globalIssueGenerator($userId, $businessId, $thirdPartyId, $issueData, $data['type'], 'local');

                        /**
                         * If business name replaced with the new one.
                         * Delete previously stored reviews
                         * Store new reviews in system.
                         */
                        if ($thirdPartyId && $isNameChanged) {
                            TripadvisorReview::where('third_party_id', $thirdPartyId)->delete();
                            StatTracking::where('third_party_id', $thirdPartyId)->delete();
                            /**
                             * Only save reviews in system
                             * if business is updated VIA Manual connect
                             */
                            if (!empty($userReviews) && !empty($request->get('targetUrl'))) {
                                $this->storeUserReviews($userReviews, $thirdPartyId, $data['type'], $request);
                            }
                        }

                        return $this->helpReturn("Tripadvisor Response.", $tripAdvisorResult);
                    }
                }
                else if ($responseCode == 404 && empty($request->get('targetUrl'))) {

                    /**
                     * User does not change the Business Name but If we'll not get any Business on change Phone or field
                     * if any chance occured then we'll not delete user existing business because user does not want
                     * to delete that business
                     */
                    if (empty($request->get('isNameChanged')) && !empty($tripAdvisorResult['name'])) {
                        Log::info("no need to delete a business record and generate an issue");
                        return $this->helpError(404, 'Business record not found.');
                    }


                    /**
                     * we make sure that this condition only worked if we
                     * are using this method from Business form not by manual connect/
                     * because manual connect has not to again compare/delete.
                     *
                     */
                    /**
                     * name issue not generate in third party master row,
                     * It will only generate into userissues table.
                     */
                    $thirdPartyObj = new TripadvisorMaster();
                    $insertIssue = [
                        [
                            'key' => 'name',
                            'userID' => $userId,
                            'business_id' => $businessId,
                            'issue' => 2,
                            'type' => $data['type']
                        ]
                    ];
                    $this->compareThirdPartyRecord($insertIssue);
                    /**
                     * delete previous stored business trace from >> third_party-master
                     *  first business was present & second time business not found on update
                     * time, so delete the business.
                     */
                    $thirdPartyObj->delThirdPartyBusiness($businessId, $data['type']);
                }

                return $this->helpError(404, 'Business record not found.');
            });
        } catch (Exception $exception) {
            Log::info(" tripadvisor entity > updateThirdPartyMaster >> " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * @param $userId
     * @param $businessId
     * @param $thirdPartyId
     * @param $arrayData
     * @param $type
     * @param string $module (local, social)
     * @return mixed
     */
    public function thirdPartyCompare($userId, $businessId, $thirdPartyId, $arrayData, $type, $module = 'local')
    {
        try {
            $compareData = [
                [
                    'key' => 'phone',
                    'value' => $arrayData['phone'][0],
                    'userID' => $userId,
                    'business_id' => $businessId,
                    'third_party_id' => $thirdPartyId,
                    'issue' => $arrayData['phone'][1],
                    'type' => $type
                ],
                [
                    'key' => 'address',
                    'value' => $arrayData['address'][0],
                    'userID' => $userId,
                    'business_id' => $businessId,
                    'third_party_id' => $thirdPartyId,
                    'issue' => $arrayData['address'][1],
                    'type' => $type
                ],
                [
                    'key' => 'website',
                    'value' => $arrayData['website'][0],
                    'userID' => $userId,
                    'business_id' => $businessId,
                    'third_party_id' => $thirdPartyId,
                    'issue' => $arrayData['website'][1],
                    'type' => $type
                ]
            ];
            $this->compareThirdPartyRecord($compareData, $module);
        } catch (Exception $e) {
            Log::info("tripadvisorentity > thirdPartyCompare >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }
    /**
     * @param $result
     * @param $reviewslinkedWith (third_party_id)
     * @param null $type
     * @param string $request
     * @param string $job
     * @return int|mixed
     */
    public function storeUserReviews($result, $reviewsLinkedWith, $type = Null, $request = '', $job = '')
    {
        try {
            $reviewData = [];
            $i = 0;
            foreach ($result as $review) {
                if ($job == 'cronjob' && $type == 'Google Places') {
                    $tripRecord = '';
                    $tripRecord = TripadvisorReview::where('third_party_id', $reviewsLinkedWith)->where('type', 'Google Places')->where('review_url', $review['ReviewURL'])->first();
                    if (!empty($tripRecord)) {

                    } else {

                        $reviewData[$i]['third_party_id'] = $reviewsLinkedWith;
                        $reviewData[$i]['reviewer'] = $review['UserName'];
                        $reviewData[$i]['reviewer_image'] = $review['UserImage'];
                        $reviewData[$i]['message'] = $review['Comment'];
                        $reviewData[$i]['review_url'] = $review['ReviewURL'];
                        $reviewData[$i]['rating'] = $review['Rating'];
                        $reviewData[$i]['review_date'] = $review['ReviewDate'];
                        // convert time to USA standards mm-dd-yy
                        $convertdate = AgoFormatConvertInDateFormat($reviewData[$i]['review_date']);
                        $reviewData[$i]['type'] = $type;
                        $firstInsertId = TripadvisorReview::insertGetId($reviewData[$i]);
                        $firstIdArray[] = $firstInsertId;
                    }
                }
                elseif($type == 'Zocdoc' || $type == 'Healthgrades' || $type == 'Ratemd') {

                    $reviewData[$i]['third_party_id'] = $reviewsLinkedWith;

                    $reviewData[$i]['reviewer'] = getIndexedvalue($review, 'author');
                    $reviewData[$i]['message'] = getIndexedvalue($review, 'comment');

                    $reviewData[$i]['rating'] = getIndexedvalue($review, 'overall');

                    $reviewData[$i]['review_date'] = getIndexedvalue($review, 'date');

                    $convertdate = AgoFormatConvertInDateFormat($reviewData[$i]['review_date']);


                    $reviewData[$i]['review_date'] = getFormattedDate($convertdate);

                    if (!empty($review['reviewID'])) {
                        $reviewData[$i]['review_unique_identifier'] = $review['reviewID'];
                    }

                    $reviewData[$i]['type'] = $type;
                }
                else {
                    $reviewData[$i]['third_party_id'] = $reviewsLinkedWith;
                    $reviewData[$i]['reviewer'] = $review['UserName'];
                    $reviewData[$i]['reviewer_image'] = $review['UserImage'];
                    $reviewData[$i]['message'] = $review['Comment'];
                    $reviewData[$i]['review_url'] = $review['ReviewURL'];
                    $reviewData[$i]['rating'] = $review['Rating'];
                    $reviewData[$i]['review_date'] = $review['ReviewDate'];
                    // convert time to USA standards mm-dd-yy
                    $convertdate = AgoFormatConvertInDateFormat($reviewData[$i]['review_date']);
                    $reviewData[$i]['review_date'] = getFormattedDate($convertdate);

                    if (!empty($review['ReviewID'])) {
                        $reviewData[$i]['review_unique_identifier'] = $review['ReviewID'];
                    }
                    $reviewData[$i]['type'] = $type;
                }
                $i++;
            }

            if (isset($firstIdArray[0])) { //this case is for google place
                $result = $firstIdArray[0];
                Log::info('google place last inserted id');
                $firstIdArray = null;
            } else if (!empty($reviewData)) {
                $result = bulk_insert("third_party_review", $reviewData);
            } else {
                $result = 0;
            }

            if ($job == 'cronjob' || !empty($request->targetUrl)) {
                $request->request->add(['first_review_id' => $result]);
            }

            $request->request->add(['type' => $type]);

            if ($result != 0 && $request != '') {
                $dashboardObj = new DashboardEntity();
                $dashboardObj->countHistoricalData($request);
            }
            return $result;
        } catch (Exception $e) {
            Log::info("tripadvisorentity > storeUserReviews >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * Table Affect:
     * User_issues (business_issues)
     *
     * Store, Update
     * Store: method create new user issues
     * Update: Method delete user issues if issue resolved during update time.
     *
     * Compare & store data in user_issuesTable.
     * @param $data (userID, $key (phone,address), $value, business_id, issue)
     * e.g:
     *   [
     *     'key' => 'phone', 'value' => $data['phone'], 'userID' => $request->get('userID'),
     *     'business_id' => $request->get('business_id'), 'issue' => 4
     *   ]
     * @param string $module (local, social)
     * @return mixed
     */
    public function compareThirdPartyRecord($data, $module = 'local')
    {
        try {
            $userIssuesObj = new UserIssues();

            // checking if name exist
            $nameIssue = array_search('name', array_column($data, 'key'));

            // this is only to keep that we've to delete issues except this array.
            $issueList = [];
            $nameIssueDeleted = 0;

            foreach ($data as $index => $row) {
                $flag = '';
                $oldIssue = '';
                $user = $row['userID'];

                // get business id from data
                $businessId = $row['business_id'];

                // third party id is using for both module (Local,social) to keep track it has third party id or not/
                $thirdPartyId = (!empty($row['third_party_id'])) ? $row['third_party_id'] : NULL;

                /**
                 *  name issue not found then need to re-compare
                 *  if name not exist then
                 * 1) delete name issue if stored in user_issues
                 * 2) store new issues if found
                 */
                if ($nameIssue === false) {
                    Log::info("1");
                    if ($index == 0) {
                        Log::info("2");

                        // get business record against this id.
                        $businessRecord = Business::find($businessId);
                    }
                    /**
                     * Delete third party name issue where associate with current third party.
                     * because new issues going to generate by replace name issues
                     */
                    $nameIssueNumber = '';
                    /**
                     * All conditional issues are linked to name
                     * issue with different type of issues.
                     */
                    if (strtolower($row['type']) == 'website') {
                        // website missing
                        $nameIssueNumber = 1;
                    } elseif (strtolower($row['type']) == 'tripadvisor') {
                        // not listed issue
                        $nameIssueNumber = 2;
                    } else if (strtolower($row['type']) == 'google places') {
                        // not listed issue
                        $nameIssueNumber = 3;
                    } else if (strtolower($row['type']) == 'yelp') {
                        // not listed issue
                        $nameIssueNumber = 12;
                    } else if (strtolower($row['type']) == 'facebook') {
                        // not listed issue
                        $nameIssueNumber = 17;
                    }
                    /**
                     * only delete one time
                     *
                     * Delete name issue, we have to run delete query at once
                     * because we want to assure that we have not issue twice.
                     *
                     * Name issue and other issues are not present at same time
                     */
                    if ($nameIssueNumber != '' && $nameIssueDeleted != 1) {
                        Log::info("3");
                        UserIssues::where(
                            [
                                'user_id' => $user,
                                'issue_id' => $nameIssueNumber,
                                'business_id' => $businessId,
                            ]
                        )->delete();
                        $nameIssueDeleted = 1;
                    }
                    if ($row['key'] == 'phone') {

                        $thirdValue = filterPhoneNumber($row['value']);
                        // $businessRecord['phone'] retrieve from existing business Record.
                        $businessPhone = filterPhoneNumber($businessRecord['phone']);
                        if ($thirdValue != '' && $businessPhone != '') {
                            $thirdPartyPhone = $thirdValue;
                            // if existing business name not match with third party record
                            if ($businessPhone != $thirdPartyPhone) {
                                $phoneLength = strlen($thirdPartyPhone);
                                $userPhoneLength = strlen($businessPhone);
                                if ($userPhoneLength > $phoneLength) {
                                    $diff = $userPhoneLength - $phoneLength;
                                    $businessPhone = substr($businessPhone, $diff);
                                } elseif ($userPhoneLength < $phoneLength) {
                                    $diff = $phoneLength - $userPhoneLength;
                                    $thirdPartyPhone = substr($thirdPartyPhone, $diff);
                                }
                                if ($thirdPartyPhone == $businessPhone) {
                                    // matched issue
                                    $flag = 'matched';
                                    $issue = $row['issue'];
                                } else {
                                    // create issue
                                    $flag = 'create';
                                    $issue = $row['issue'];
                                }
                            } else {
                                // yes this matched. don't need to create user issue.
                                $flag = 'matched';
                                $issue = $row['issue'];
                            }
                            $issueList[] = $issue;
                        } else {
                            $flag = 'create';
                            $issue = $row['issue'];
                        }
                        $oldIssue = !empty($row['oldIssue']) ? $row['oldIssue'] : '';
                    }
                    else if ($row['key'] == 'website')
                    {

                        $thirdValue = $row['value'];
                        if ($thirdValue != '') {
                            // $businessRecord['website'] retrieve from existing business Record.
                            $businessWebsite = $businessRecord['website'];
                            // if existing business website not match with third party record
                            if ($businessWebsite != getUrlDomain($thirdValue) && strtolower($businessWebsite) != getUrlDomain($thirdValue)) {
                                $issue = $row['issue'];
                                $flag = 'create';
                            } else {
                                // yes this matched. don't need to create user issue.
                                $flag = 'matched';
                                $issue = $row['issue'];
                            }
                            $issueList[] = $issue;
                        } else {
                            $flag = 'create';
                            $issue = $row['issue'];
                        }
                        $oldIssue = !empty($row['oldIssue']) ? $row['oldIssue'] : '';
                    }
                    else if ($row['key'] == 'address') {
                        // get third party address.
                        $address = $row['value'];
                        $userAddressHold = $businessRecord['street'];
                        if ($address != '') {
                            $addressHolder = [];
                            if ($address == $userAddressHold) {
                                $issue = $row['issue'];
                                $flag = 'matched';
                            } else {
                                $userAddressHold = explode(' ', str_replace(",", "", $userAddressHold));
                                $address = str_replace(",", "", $address);
                                $matchedWordsAllow = count(explode(" ", $address));
                                if ($matchedWordsAllow > 3) {
                                    $matchedWordsAllow = $matchedWordsAllow / 2;
                                    $matchedWordsAllow = (int)$matchedWordsAllow;
                                }
                                // compare address with third party.
                                foreach ($userAddressHold as $userAddress) {
                                    /**
                                     * if any word not matched with "address".
                                     */
                                    if (hasWord($userAddress, $address) == 1) {
                                        $matchedWordsAllow--;
                                    }
                                    if ($matchedWordsAllow == 0) {
                                        break;
                                    }
                                }
                                if ($matchedWordsAllow == 0) {
                                    $issue = $row['issue'];
                                    $flag = 'matched';
                                } else {
                                    // issue created
                                    $issue = $row['issue'];
                                    $flag = 'create';
                                }
                            }
                            $issueList[] = $row['issue'];
                        } else {
                            $flag = 'create';
                            $issue = $row['issue'];
                        }
                        $oldIssue = !empty($row['oldIssue']) ? $row['oldIssue'] : '';
                    }
                    elseif ($row['key'] == 'rating') {
                        if ($row['value'] == '' || $row['value'] < 4) {
                            $flag = 'create';
                        } else {
                            $flag = 'matched';
                        }
                        $issue = $row['issue'];
                    }
                    elseif ($row['key'] == 'reviews') {
                        Log::info("review val " . $row['value']);
                        Log::info("review Isue " . $row['issue']);
                        if ($row['value'] == '' || $row['value'] < 10) {
                            $flag = 'create';
                        } else {
                            $flag = 'matched';
                        }
                        $issue = $row['issue'];
                    }
                    elseif ($row['key'] == 'page_speed' || $row['key'] == 'mobile_speed') {
                        $flag = 'create';
                        $issue = $row['issue'];
                    }
                    elseif ($row['key'] == 'profile_photo' || $row['key'] == 'cover_photo' || $row['key'] == 'google_analytics' || $row['key'] == 'title_tags') {
                        // if values are empty then create, if not then matched
                        if ($row['value'] == '') {
                            $flag = 'create';
                            $issue = $row['issue'];
                        } else {
                            // yes this matched. don't need to create user issue.
                            $flag = 'matched';
                            $issue = $row['issue'];
                        }
                    }

                    Log::info("4");
                }
                else {
                    Log::info("20");
                    // name issue found & don't need to re-compare other things
                    $issue = $data[$nameIssue]['issue'];
                    // get system issues of type which related to current third party api.
                    $thirdPartyIssuesList = IssuesList::where('site', $row['type'])->where('issue_id', '!=', $issue)->get();
                    foreach ($thirdPartyIssuesList as $thirdPartyIssue) {
                        // delete all issues of current type which not equal to current issue.
                        UserIssues::where(
                            [
                                'user_id' => $user,
                                'business_id' => $businessId,
                                'issue_id' => $thirdPartyIssue['issue_id']
                            ]
                        )->delete();
                    }
                    $flag = 'create';
                }

                if ($flag != '') {
                    $existingIssueRecord = $userIssuesObj->userSpecificIssue($user, $businessId, $issue);
                    // delete old issue
                    if (!$existingIssueRecord && !empty($oldIssue)) {
                        // check if old issue exist in database.
                        $oldIssueRecord = $userIssuesObj->userSpecificIssue($user, $businessId, $oldIssue);
                        if ($oldIssueRecord) {
                            UserIssues::find($oldIssueRecord['id'])->delete();
                        }
                    }
                }
                // check if issue found then update else create it.
                if ($flag == 'create') {
                    /**
                     * If issue not already exist.
                     * if issue not found. then >>>> create it.
                     * else if issue not previously exist and re-found then don't do anything.
                     */
                    if (!$existingIssueRecord) {
                        if ($module == 'local') {
                            $thirdPartyKey = 'third_party_id';
                            $moduleType = 'local-marketing';
                        } else if ($module == 'social') {
                            $thirdPartyKey = 'social_media_id';
                            $moduleType = 'social-media';
                        } else {
                            $thirdPartyKey = 'social_media_id';
                            $moduleType = 'website';
                        }
                        UserIssues::create(
                            [
                                'user_id' => $user,
                                'issue_id' => $issue,
                                'business_id' => $businessId,
                                $thirdPartyKey => $thirdPartyId,
                                'module_type' => $moduleType
                            ]
                        );
                    }
                } else if ($flag == 'matched') {
                    /**
                     * DOn't need to re-create an issue if issue resolved and we get status matched
                     * e.g: phone==phone, site==site
                     *
                     * It will benefit on deleting only when record is already exist.
                     */
                    // delete it if issue found.
                    if ($existingIssueRecord) {
                        UserIssues::find($existingIssueRecord['id'])->delete();
                    }
                }
                // if name issue found then break the loop after completing one circle.
                if ($nameIssue != '') {
                    break;
                }
            }
        } catch (Exception $e) {
            Log::info("tripadvisorentity > compareThirdPartyRecord >> " . $e->getMessage() . " > " . $e->getLine());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function notifyUsers($request)
    {

        try {

            $businessResult = DB::table('business_master as bm')
                ->join('third_party_master as tpm', 'bm.business_id', '=', 'tpm.business_id')
                ->join('user_master as usm', 'bm.user_id', '=', 'usm.id')
                ->select('bm.user_id', 'usm.first_name', 'bm.business_id', 'tpm.business_id as tripBusinessId', 'bm.name', 'third_party_id', 'page_url')
                ->where('tpm.type', 'Tripadvisor')
                ->where('bm.business_profile_status', 'completed')
                ->get();
            if (!empty($businessResult)) {
                $tripEntity = new TripAdvisorEntity();
                foreach ($businessResult as $row) {

                    $arra = ['businessKeyword' => $row->name];
                    $request->merge($arra);
                    // get business detail from trip advisor.
                    $result = $tripEntity->getBusinessDetail($request);
                    if ($result['_metadata']['outcomeCode'] == 200) {

                        $records = $result['records']['Results'];
                        $userReviews = $result['records']['Results']['ReviewsDetail'];
                        /**
                         * if user business record meet on trip advisor area
                         * update third_party_master_table
                         */
                        if ($records) {
                            /**
                             * if user has reviews of current business then also update user Review
                             * against in third_party_review table..
                             */
                            if ((!empty($userReviews))) {

                                $result = $this->storeUserReviews($userReviews, $row->third_party_id);
                                /**
                                 * send notify to user, If any new entry posted
                                 */
                                if ($result != 0) {

                                    // get tripadvisor message.
                                    $chatMasterResult = ChatMaster::select('message')->find(7);
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
                                         * 11 is linked with tripadvisor if user click tripadvisor then
                                         *we take this id to get our result.
                                         */
                                        $data = [
                                            'chat_id' => $chatid,
                                            'issue_id' => 11,
                                        ];
                                        ChatIssueLogs::create($data);
                                    }
                                }
                            }
                        }
                    }
                }
                return $this->helpReturn("Trip Advisor Cron job process completed.");
            }
        } catch (Exception $e) {
            Log::info("tripadvisorentity > notifyUsers >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }
}
