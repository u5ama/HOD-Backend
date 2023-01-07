<?php


namespace Modules\ThirdParty\Entities;


use App\Entities\AbstractEntity;
use Exception;
use GuzzleHttp\Client;
use Config;
use Log;

class ContentDiscoveryEntity extends AbstractEntity
{

    public function getSocialViralContent($request)
    {

        $buzzsumokey = Config::get('apikeys.BUZZSUMO_API_KEY');

        try {
            if (!empty($buzzsumokey) && !empty($request->q) && !empty($request->article_type) && !empty($request->num_days)) {
                return $this->getSocialData($buzzsumokey, $request->q, $request->article_type, $request->num_days);
            } else {
                return $this->helpError(1, 'Invalid Inputs');
            }

        } catch (Exception $exception) {
            Log::info(" getSocialViralContent > " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getSocialData($apiKey, $keyword, $articleType, $numberOfDays)
    {
        try {

            $numberOfDay = contentDiscoveryDateConversion($numberOfDays);

            $client = new Client([]);

            $response = $client->request(
                'GET',
                'http://api.buzzsumo.com/search/articles.json',
                [
                    'query' => [
                        'api_key' => $apiKey,
                        'q' => $keyword,
                        'num_days' => $numberOfDay,
                        'article_type' => $articleType,
                    ],
                ]
            );


            $responseData = json_decode($response->getBody()->getContents(), true);

            $records = $responseData['results'];

            if (empty($records)) {

                return $this->helpError(404, 'Record not found.', $records);

            }

            if ($response->getStatusCode() == 200) {

                foreach ($records as $index => $data) {


                    try {
                        $PreviewLinkApi = $client->request(
                            'GET',
                            'http://api.linkpreview.net',
                            [
                                'query' => [
                                    'key' => '5ca47d4e7b60f26768deaada07c5f857c492cdd966a34',
                                    'q' => $data['url'],
                                ],
                            ]
                        );

                        $previewUrlDetail = json_decode($PreviewLinkApi->getBody()->getContents(), true);
                    } catch (Exception $e) {
                        Log::info('Link Preview request exceed');
                        Log::info($e->getMessage());
                    }
                    $date = $data['published_date'];
                    $convertddate = date('Y-m-d', $date);
                    $viralcontentData[] = [
                        'title' => $data['title'],
                        'website' => $data['domain_name'],
                        'article_url' => $data['url'],
                        'preview_title' => isset($previewUrlDetail['title']) ? $previewUrlDetail['title'] : '',
                        'preview_description' => isset($previewUrlDetail['description']) ? $previewUrlDetail['description'] : '',
                        'preview_image' => isset($previewUrlDetail['image']) ? $previewUrlDetail['image'] : '',
                        'facebook_share' => isset($data['total_facebook_shares']) ? $data['total_facebook_shares'] : 0,
                        'twitter_share' => isset($data['twitter_shares']) ? $data['twitter_shares'] : 0,
                        'linkedin_share' => isset($data['linkedin_shares']) ? $data['linkedin_shares'] : 0,
                        'pinterest_share' => isset($data['pinterest_shares']) ? $data['pinterest_shares'] : 0,
                        'date' => $convertddate,

                    ];
                }
                return $this->helpReturn("Viral Content Discovery Result.", $viralcontentData);
            }

            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {

            Log::info(" getSocialData >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }

    }
}
