<?php
use Carbon\Carbon;
use FuzzyWuzzy\Fuzz;
use Modules\Business\Http\Controllers\HomeController;

/**
 * @param $reason
 * @param $message
 * @return array
 */
function make_error_object($reason, $message)
{
    return ['map' => $reason, 'message' => $message];
}

function imageReturn($image)
{
    return asset('public/images/'.$image);
}

/**
 * @param $messageBagArray
 * @return array
 */
function convert_laravel_input_errors($messageBagArray)
{
    $errors = [];
    foreach ($messageBagArray as $key => $message) {
        $errors[] = make_error_object($key, $message[0]);
    }
    return $errors;
}

/**
 *
 * convert standard error to extension error
 *
 * @param $errors
 * @return array
 */
function convert_laravel_input_errors_to_extension_errors($errors)
{
    return ['error' => $errors[0]['message']];
}

function k_arrayIndexCheck($array, $index)
{
    $value = false;

    if(isset($array[$index]) && $array[$index] != '')
    {
        $value = $array[$index];
    }
    return $value;
}

function messageTimeFormat($timestamp)
{
    $strTime = strtotime($timestamp); // '2017-05-15 11:29:54'

    $time = time() - $strTime; // to get the time since that moment
    $time = ($time<1)? 1 : $time;

    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        172800 => 'one day ago',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    if($time <= 86400)
    {
        $messageTime = 'Today ' . date('g:i A', $strTime);
    }
    else if($time > 86400 && $time <= 172800)
    {
        $messageTime = 'yesterday ' . date('g:i A', $strTime);
    }
    else
    {
        $messageTime = date('M j, Y g:i A', $strTime);
    }

    return $messageTime;
}

function stringEncodeForAPI($name)
{
    $name = str_replace("&", "%26", $name);
    return $name;
}

function bulk_insert($table, $data)
{
    $SQLQuery = '';
    $SQLCols = array();

    $queriesArray = [];

    $columnsCount = -1;
    $previousSQLCols = [];

    foreach ($data as $dataRow) {

        $SQLCols = array_keys($dataRow);

        if ($columnsCount != count($SQLCols)) {
            if ($columnsCount != -1) {
                $SQLQuery = rtrim($SQLQuery, ',');
                $SQLQuery .= " ON DUPLICATE KEY UPDATE \n";
                $OnDuplicateSet = array();
                foreach ($previousSQLCols as $Column) {
                    $OnDuplicateSet[] = "`{$Column}`=VALUES(`{$Column}`)";
                }
                $SQLQuery .= implode(", \n", $OnDuplicateSet) . ";";
                $queriesArray[] = $SQLQuery;
            }
            $columnsCount = count($SQLCols);
            $previousSQLCols = $SQLCols;
            $SQLQuery = 'INSERT INTO `' . $table . '`(' . implode(",", $SQLCols) . ') VALUES ';
        }
        $vals = [];
        foreach ($dataRow as $key => $value) {
            $vals[] = $value ? DB::getPdo()->quote((string)$value) : "NULL";
        }
        $SQLValue = '(' . implode(',', $vals) . ')';
        $SQLQuery .= "" . $SQLValue . ",";

    }

    $SQLQuery = rtrim($SQLQuery, ',');


    $SQLQuery .= " ON DUPLICATE KEY UPDATE \n";
    $OnDuplicateSet = array();
    foreach ($SQLCols as $Column) {
        $OnDuplicateSet[] = "`{$Column}`=VALUES(`{$Column}`)";
    }
    $SQLQuery .= implode(", \n", $OnDuplicateSet) . ";";
    $queriesArray[] = $SQLQuery;

    $isLastInserted = 0;

    foreach ($queriesArray as $query) {
        DB::insert(ltrim($query, ";"));

        /**
         * This id keep track what if any new record inserted in a table.
         */
        $isLastInserted = DB::getPdo()->lastInsertId();
    }

    return $isLastInserted;
}

function urlDomainChecker($url)
{
    $code = 200;
    $status = 'unknown';


    if (preg_match('/http:\/\/(www\.)*vimeo\.com\/.*/', $url)) {
        // do vimeo stuff
        $status = 'vimeo';
    } else if (
    preg_match(
        '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/',
        $url)
    ) {
        // do youtube stuff
        $status = 'youtube';
    } else {
        $code = '403';
    }

    $statusData = [
        'code' => $code,
        'status' => $status,
    ];

    return $statusData;

}

function reformatText($string, $name)
{
    if(empty($name))
    {
        return str_replace(' <first_name>', ucfirst($name), $string);
    }

    return str_replace('<first_name>', ucfirst($name), $string);
}

function getUrlDomain($url)
{
    $parseUrl = parse_url(trim($url));

    if (!empty($parseUrl['host'])) {
        $url = 'http://' . $parseUrl['host'];
    } else if (!empty($parseUrl['path'])) {
        $url = 'http://' . $parseUrl['path'];
    }

    return str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));
}

function phonFormatter($phone)
{
    $formatToReplace = array("+", " ", "-","(",")");
    $replaceFormat   = array("", "", "","","");

    return str_replace($formatToReplace, $replaceFormat, $phone);
}

/**
 * Get time according to given timezone.
 *
 * @param $timeZone
 * @param $timeStamp
 * @return false|string
 */
function utcZoneTime($timeZone, $timeStamp)
{
    if($timeZone)
    {
        $timeZone = str_replace('GMT', '', $timeZone);

        $time = strtotime($timeStamp); // get unix time stamp of given timestamp

        return gmdate( "Y-m-j H:i:s", $time + 3600*( $timeZone+date("I") ) );
    }

    return $timeStamp;
}

function getIndexedvalue($array, $index, $return = NULL)
{
    return !empty( $array[$index] ) ? $array[$index] : $return;
}

function AgoFormatConvertInDateFormat($string)
{
    $stringArray = explode(" ",$string);
    $number = $stringArray[0];
    if(isset($stringArray[1])){
        $str = $stringArray[1];
    }else{ // this is yelp case because yelp no having second index
        $str = $string;
    }

    if ($number == 'a') {
        $number = "1";
    }

    if($str == 'second' || $str == 'Second' || $str == 'seconds' || $str == 'Seconds'){
        $currentDate = Carbon::now();
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'minute' || $str == 'Minute' || $str == 'minutes' || $str == 'Minutes'){
        $currentDate = Carbon::now();
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'hour' || $str == 'Hour' || $str == 'hours' || $str == 'Hours'){
        $currentDate = Carbon::now();
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'today' || $str == 'Today'){
        $currentDate = Carbon::now();
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'yesterday' || $str == 'Yesterday'){
        $currentDate = Carbon::now()->subDays(1);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'Days' || $str == 'days' || $str == 'Day' || $str == 'Days'){
        $currentDate = Carbon::now()->subDays($number);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'Week' || $str == 'week' || $str == 'Weeks' || $str == 'weeks'){
        $currentDate = Carbon::now()->subWeek($number);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'Month' || $str == 'month' || $str == 'Months' || $str == 'months'){
        $currentDate = Carbon::now()->subMonth($number);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($str == 'year' || $str == 'Year' || $str == 'years' || $str == 'Years'){
        $currentDate = Carbon::now()->subYear($number);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    else if($string == 'in the last day'){
        $currentDate = Carbon::now()->subDays(1);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    //special cases for google places
    else if($string == 'in the last week'){
        $currentDate = Carbon::now()->subWeek(1);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');

    }else if($string == 'just now' || $string == 'Just now' || $string == 'just Now'){
        $currentDate = Carbon::now();
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');

    }
    else if($string == 'in the last month'){
        $currentDate = Carbon::now()->subMonth(1);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');

    }
    else if($string == 'in the last year' || $string == 'less_than_1_year' || $string == 'more_than_1_year'){
        $currentDate = Carbon::now()->subYear(1);
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');

    }
    else{
        $string = explode(' ',$string); // this login use for tripadvisor and yelp

        if(isset($string[0]) && isset($string[1]) && isset($string[2]) && isset($string[3])){ //remove extra content hai spaces
            $finalString = trim($string[0]);  // remove extra spaces
        }else{
            $finalString = implode(" ",$string);;
        }
        $convertdate = strtotime($finalString);
        $formatedDate = date('m-d-Y', $convertdate );
    }
    return $formatedDate;
}

function filterPhoneNumber($phone)
{
    $formatToReplace = array("+", " ", "-","(",")",".");
    $replaceFormat   = array("", "", "","","");
    $phone = str_replace($formatToReplace, $replaceFormat, $phone);

    if(is_numeric($phone))
    {
        return ltrim($phone, '0');
    }

    return '';

}

function moduleSiteList()
{
    return ['Tripadvisor', 'Google Places', 'Yelp', 'Facebook', 'Website'];

}
function moduleSocialList($required = 'main')
{
    if($required == 'main')
    {
        return ['Facebook', 'Twitter'];
    }
    return ['Twitter', 'Linkedin', 'Instagram','Facebook'];
}


/**
 * @param $day
 * @param string $extractWeek (next,previous)
 */
function extractWeekDays($day, $extractDays = 6)
{
    $timestamp = strtotime($day);
    $dateFormat = dateFormatUsing();

    for($i=0;$i<=$extractDays;$i++) {
        $date = strtotime("+$i day", $timestamp);
        $weekDates[$i]['activity_date'] = date($dateFormat, $date);
        $weekDates[$i]['count'] = 0;
    }
    return $weekDates;
}

function dateFormatUsing($format = 'Y-m-d')
{
    return $format;
}

function getFormattedDate($date, $dateFormat = '')
{
    if($dateFormat == '')
    {
        $dateFormat = dateFormatUsing();
    }
    $timestamp = strtotime(str_replace('-', '/', $date));

    if ($timestamp === FALSE) {
        $timestamp = strtotime(str_replace('/', '-', $date));
    }

    return date('Y-m-d', $timestamp);
}

function contentDiscoveryDateConversion($date)
{
    if ($date == 'Last 24 Hours') {
        $numberOfDay = 1;
        // $currentDate = Carbon::now();
        // $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    } else if ($date == 'Last Week') {
        $numberOfDay = 7;
        //  $currentDate = Carbon::now()->subWeek(1);
        // $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    } else if ($date == 'Last Month') {
        $numberOfDay = 30;
        // $currentDate = Carbon::now()->subMonth(1);
        // $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    } else if ($date == 'Last 6 Months') {
        $numberOfDay = 282;
        // $currentDate = Carbon::now()->subMonth(6);
        // $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    } else if ($date == 'Last Year') {
        $numberOfDay = 365;
        // $currentDate = Carbon::now()->subYear(1);
        // $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    } else if ($date == 'Last 2 Years') {
        $numberOfDay = 730;
        // $currentDate = Carbon::now()->subYear(2);
        // $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m-d-Y');
    }
    return $numberOfDay;
}

/**
 * @param $newNumber ($newNumber, today)
 * @param int $originalNumber ($newNumber, yesterday)
 * @param string $type
 * @return array
 */
function insightTitle($newNumber = 0, $originalNumber = 0, $type = 'week', $category ='RV', $id = '')
{
    $data['objective'] = '';
    $data['insightDescription'] = '';

    if($category == 'RV' || $category == 'LK') {
        if ($type == 'week') {
            $increase = $newNumber - $originalNumber;
            if ($originalNumber == 0) {
                $originalNumber = 1;
            }
            $increase = $increase / $originalNumber * 100;
            $increase = round($increase, 2);

            if ($increase == 0 || strpos($increase, '-') !== false) {
                $increase = str_replace('-', '', $increase);

                $data['insightTitle'] = "Down $increase% from previous week";
                $data['insightStatus'] = "down";
            } else {
                $data['insightTitle'] = "Up $increase% from previous week";
                $data['insightStatus'] = "up";
            }
        } else {
            $diff = $newNumber - $originalNumber;
            if ($diff == 0 || strpos($diff, '-') !== false) {
                $diff = str_replace('-', '', $diff);
                $data['insightTitle'] = "Down $diff from yesterday";
                $data['insightStatus'] = "down";
            } else {
                $data['insightTitle'] = "Up $diff from yesterday";
                $data['insightStatus'] = "up";
            }
        }

        if($category == 'LK')
        {
            $data['insightDescription'] = "Click here how to learn more on how you can increase your Facebook page likes.";
        }
        else
        {
            $data['insightDescription'] = "Click here how to learn more about how you can increase your reviews.";
        }
    }
    elseif($category == 'RG')
    {
        if($newNumber >= 1 && $newNumber < 3)
        {
            $data['insightTitle'] = "Poor customer rating";
            $data['insightDescription'] = "Your customers rate your services or products low. Click here for the task on how to improve your customer satisfactory rating.";
            $data['insightStatus'] = "down";
        }
        elseif($newNumber >= 3 && $newNumber < 4)
        {
            $data['insightTitle'] = "Average customer rating";
            $data['insightDescription'] = "Your customers are not exactly blown away by your service but you can do better. Click here to read the task on how to improve your customer satisfactory rating.";
            $data['insightStatus'] = "average";
        }
        elseif($newNumber >= 4 && $newNumber < 5)
        {
            $data['insightTitle'] = "Good customer rating";
            $data['insightDescription'] = "Your customers are happy with your services but there’s still a little room for improvement. Click here to read the task on how to improve your customer satisfactory rating.";
            $data['insightStatus'] = "up";
        }
        elseif($newNumber == 5)
        {
            $data['insightTitle'] = "Perfect customer rating!";
            $data['insightDescription'] = "";
            $data['insightStatus'] = "up";
        }
        else
        {
            $data['insightTitle'] = "Not received any feedback";
            $data['insightDescription'] = "You have not received any feedback. Click here to learn how to get your customers to leave feedback on your site.";
            $data['insightStatus'] = "down";
            $data['objective'] = $id;
        }
    }
    elseif($category == 'analytics' || $category == 'PV')
    {
        if ($type == 'week') {
            $diff = $newNumber/7;
            $diff = round($diff, 2);

            $data['insightTitle'] = "Average of $diff pageviews <br> for last 7 days";
            $data['insightStatus'] = "up";
            $data['insightDescription'] = "Click here to learn how you can increase your website traffic.";
        }
        elseif ($type == 'all') {
            $diff = $newNumber/30;
            $diff = round($diff, 2);

            $data['insightTitle'] = "Average of $diff pageviews <br> for last 30 days";
            $data['insightStatus'] = "up";
            $data['insightDescription'] = "Click here to learn how you can increase your website traffic.";
        }
        elseif ($type == 'day') {
            $diff = $newNumber - $originalNumber;
            if ($diff == 0 || strpos($diff, '-') !== false) {
                $diff = str_replace('-', '', $diff);
                $data['insightTitle'] = "Down $diff from yesterday";
                $data['insightStatus'] = "down";
            } else {
                $data['insightTitle'] = "Up $diff from yesterday";
                $data['insightStatus'] = "up";
            }

            $data['insightDescription'] = "Click here how to learn more on how you can increase your website traffic.";
        }
        else
        {
            $data['insightTitle'] = "Google Analytics not detected <br> on website.";
            $data['insightDescription'] = "We have not detected your Google Analytics code on your website. <View task> on how to add GA to your website.";
            $data['insightStatus'] = "down";
        }
    }
    else
    {
        if($newNumber >= 0 && $newNumber < 70)
        {
            $data['insightTitle'] = "Poor optimization";
            $data['insightDescription'] = "This page is not optimized and is likely to deliver a slow user experience. Click here to read the task on how to optimize your website.";
            $data['insightStatus'] = "down";
        }
        elseif($newNumber >= 70 && $newNumber < 85)
        {
            $data['insightTitle'] = "Average optimization";
            $data['insightDescription'] = "This page is missing some common performance optimizations that may result in a slow user experience. Click here to read the task on how to optimize your website.";
            $data['insightStatus'] = "average";
        }
        elseif($newNumber >= 85 && $newNumber <= 100)
        {
            $data['insightTitle'] = "Great optimization";
            $data['insightDescription'] = "“Nice work! This page applies most performance best practices and should deliver a good user experience. See how you can optimize your website further. Click here.";
            $data['insightStatus'] = "up";
        }
    }

    $data['insightDescription'] = str_replace('Click here', '<Click here>', $data['insightDescription']);

    if($data['insightDescription'] != '' && $data['objective'] == '')
    {
        $data['objective'] = $id;
    }

    if( empty($data['insightTitle']) )
    {
        return $data = [
            'insightTitle' => '',
            'insightDescription' => '',
            'insightStatus' => '',
            'objective' => '',
        ];
    }
    return $data;
}

function hasWord($word, $text) {
    $patt = "/(?:^|[^a-zA-Z0-9])" . preg_quote($word, '/') . "(?:$|[^a-zA-Z0-9])/i";
    return preg_match($patt, $text);
}

function randomString($length = 8)
{
    $str = "";
    $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
}

function getDynamicAppSupportEmail()
{
    return "support@HeroesofDigital.io";
}

function appName()
{
    return 'Heroes of Digital';
}

function getDynamicAppName()
{
    return 'Heroes of Digital';
}

function getThirdPartyTypeLongToShortForm($type)
{
    $type =  str_replace(" ", "", strtolower($type));

    if ($type == 'googleplaces') {
        $shortType = 'GP';
    } else if ($type == 'facebook') {
        $shortType = 'FB';
    }
    return $shortType;
}

function getThirdPartyTypeShortToLongForm($type)
{
    $longType = '';
    if ($type == 'GP') {
        $longType = 'Google';
    } else if ($type == 'FB') {
        $longType = 'Facebook';
    }

    return $longType;
}

function compareStringGetSubSetResult($scrapperName,$userName)
{

    $userName = preg_replace("/[^a-zA-Z]/", "", strtolower($userName));
    $scrapperName = preg_replace("/[^a-zA-Z]/", "", strtolower($scrapperName));
    Log::info('original business name '.$userName);
    Log::info('scrapper normalize name'.$scrapperName);

    if (strpos($scrapperName, $userName) !== false) {
        $res =  'true';
    }else{
        $res = 'false';
    }
    return $res;
}

function userName($name = '')
{
    if(!empty(Session('user_data')))
    {
        $userData = Session('user_data');
        if($name != '')
        {
            $name = !empty($userData[$name]) ? $userData[$name] : '';
        }
        else
        {
            $name = $userData['first_name'] . ' ' . $userData['last_name'];
        }
        return $name;
    }

    return '';
}

function getCRMDataHelper()
{
    $homeObj = new HomeController();
    return $homeObj->getCrmModuleData();
}

function testingtheco()
{
    return "hi";
}

function get_longest_common_subsequence($string_1, $string_2)
{
    $string_1_length = strlen($string_1);
    $string_2_length = strlen($string_2);
    $return          = '';

    if ($string_1_length === 0 || $string_2_length === 0)
    {
        // No similarities
        return $return;
    }

    $longest_common_subsequence = array();

    // Initialize the CSL array to assume there are no similarities
    $longest_common_subsequence = array_fill(0, $string_1_length, array_fill(0, $string_2_length, 0));

    $largest_size = 0;

    for ($i = 0; $i < $string_1_length; $i++)
    {
        for ($j = 0; $j < $string_2_length; $j++)
        {
            // Check every combination of characters
            if ($string_1[$i] === $string_2[$j])
            {
                // These are the same in both strings
                if ($i === 0 || $j === 0)
                {
                    // It's the first character, so it's clearly only 1 character long
                    $longest_common_subsequence[$i][$j] = 1;

                }
                else
                {
                    // It's one character longer than the string from the previous character
                    $longest_common_subsequence[$i][$j] = $longest_common_subsequence[$i - 1][$j - 1] + 1;
                }

                if ($longest_common_subsequence[$i][$j] > $largest_size)
                {
                    // Remember this as the largest
                    $largest_size = $longest_common_subsequence[$i][$j];
                    // Wipe any previous results
                    $return       = '';
                    // And then fall through to remember this new value
                }

                if ($longest_common_subsequence[$i][$j] === $largest_size)
                {
                    // Remember the largest string(s)
                    $return = substr($string_1, $i - $largest_size + 1, $largest_size);
                }
            }
            // Else, $CSL should be set to 0, which it was already initialized to
        }
    }

    // Return the list of matches
    return $return;
}

function getDomain()
{
    return url('/');
}

function backDomain()
{
//    return uri();
    return 'https://staging-api.heroesofdigital.io/';
}

function myDomain()
{
     // $frontURL = 'https://hod.test';
     // $frontURL = 'http://localhost:4200';
      // $frontURL = 'http://localhost:4200';
      $frontURL = 'https://staging-frontend.heroesofdigital.io/';
    return $frontURL;
}

function frontUrl()
{
     // return 'http://localhost:4200/';
     return 'https://staging-frontend.heroesofdigital.io/';
}

function getDomainName()
{
    return "Heroes of Digital";
}

function scriptVersion()
{
    return time();
}

function getDomainHeading()
{
    appName();
}

function decSerBase($str){
    return unserialize(base64_decode($str));
}

function thirdPartySources($exclude = '')
{
    $sources = [
        'Google Places',
        'Facebook',
    ];

    return $sources;
}

function uploadImagePath($file)
{
    return url('storage/app/'.$file);
}

function size_as_kb($yoursize) {
    $size_kb = round($yoursize/1024);
    return $size_kb;
}

function lang_code_to_lnag ($code ){
    $lang = null;
    if( $code == 'ab' ) $lang = 'Abkhazian';
    if( $code == 'aa' ) $lang = 'Afar';
    if( $code == 'af' ) $lang = 'Afrikaans';
    if( $code == 'sq' ) $lang = 'Albanian';
    if( $code == 'am' ) $lang = 'Amharic';
    if( $code == 'ar' ) $lang = 'Arabic';
    if( $code == 'an' ) $lang = 'Aragonese';
    if( $code == 'hy' ) $lang = 'Armenian';
    if( $code == 'as' ) $lang = 'Assamese';
    if( $code == 'ay' ) $lang = 'Aymara';
    if( $code == 'az' ) $lang = 'Azerbaijani';
    if( $code == 'ba' ) $lang = 'Bashkir';
    if( $code == 'eu' ) $lang = 'Basque';
    if( $code == 'bn' ) $lang = 'Bengali (Bangla)';
    if( $code == 'dz' ) $lang = 'Bhutani';
    if( $code == 'bh' ) $lang = 'Bihari';
    if( $code == 'bi' ) $lang = 'Bislama';
    if( $code == 'br' ) $lang = 'Breton';
    if( $code == 'bg' ) $lang = 'Bulgarian';
    if( $code == 'my' ) $lang = 'Burmese';
    if( $code == 'be' ) $lang = 'Byelorussian (Belarusian)';
    if( $code == 'km' ) $lang = 'Cambodian';
    if( $code == 'ca' ) $lang = 'Catalan';
    if( $code == 'zh' ) $lang = 'Chinese';
    if( $code == 'zh-Hans' ) $lang = 'Chinese (Simplified)';
    if( $code == 'zh-Hant' ) $lang = 'Chinese (Traditional)';
    if( $code == 'co' ) $lang = 'Corsican';
    if( $code == 'hr' ) $lang = 'Croatian';
    if( $code == 'cs' ) $lang = 'Czech';
    if( $code == 'da' ) $lang = 'Danish';
    if( $code == 'nl' ) $lang = 'Dutch';
    if( $code == 'en' ) $lang = 'English';
    if( $code == 'eo' ) $lang = 'Esperanto';
    if( $code == 'et' ) $lang = 'Estonian';
    if( $code == 'fo' ) $lang = 'Faeroese';
    if( $code == 'fa' ) $lang = 'Farsi';
    if( $code == 'fj' ) $lang = 'Fiji';
    if( $code == 'fi' ) $lang = 'Finnish';
    if( $code == 'fr' ) $lang = 'French';
    if( $code == 'fy' ) $lang = 'Frisian';
    if( $code == 'gl' ) $lang = 'Galician';
    if( $code == 'gd' ) $lang = 'Gaelic (Scottish)';
    if( $code == 'gv' ) $lang = 'Gaelic (Manx)';
    if( $code == 'ka' ) $lang = 'Georgian';
    if( $code == 'de' ) $lang = 'German';
    if( $code == 'el' ) $lang = 'Greek';
    if( $code == 'kl' ) $lang = 'Greenlandic';
    if( $code == 'gn' ) $lang = 'Guarani';
    if( $code == 'gu' ) $lang = 'Gujarati';
    if( $code == 'ht' ) $lang = 'Haitian Creole';
    if( $code == 'ha' ) $lang = 'Hausa';
    if( $code == 'he' ) $lang = 'Hebrew';
    if( $code == 'iw' ) $lang = 'Hebrew';
    if( $code == 'hi' ) $lang = 'Hindi';
    if( $code == 'hu' ) $lang = 'Hungarian';
    if( $code == 'is' ) $lang = 'Icelandic';
    if( $code == 'io' ) $lang = 'Ido';
    if( $code == 'id' ) $lang = 'Indonesian';
    if( $code == 'in' ) $lang = 'Indonesian';
    if( $code == 'ia' ) $lang = 'Interlingua';
    if( $code == 'ie' ) $lang = 'Interlingue';
    if( $code == 'iu' ) $lang = 'Inuktitut';
    if( $code == 'ik' ) $lang = 'Inupiak';
    if( $code == 'ga' ) $lang = 'Irish';
    if( $code == 'it' ) $lang = 'Italian';
    if( $code == 'ja' ) $lang = 'Japanese';
    if( $code == 'jv' ) $lang = 'Javanese';
    if( $code == 'kn' ) $lang = 'Kannada';
    if( $code == 'ks' ) $lang = 'Kashmiri';
    if( $code == 'kk' ) $lang = 'Kazakh';
    if( $code == 'rw' ) $lang = 'Kinyarwanda (Ruanda)';
    if( $code == 'ky' ) $lang = 'Kirghiz';
    if( $code == 'rn' ) $lang = 'Kirundi (Rundi)';
    if( $code == 'ko' ) $lang = 'Korean';
    if( $code == 'ku' ) $lang = 'Kurdish';
    if( $code == 'lo' ) $lang = 'Laothian';
    if( $code == 'la' ) $lang = 'Latin';
    if( $code == 'lv' ) $lang = 'Latvian (Lettish)';
    if( $code == 'li' ) $lang = 'Limburgish ( Limburger)';
    if( $code == 'ln' ) $lang = 'Lingala';
    if( $code == 'lt' ) $lang = 'Lithuanian';
    if( $code == 'mk' ) $lang = 'Macedonian';
    if( $code == 'mg' ) $lang = 'Malagasy';
    if( $code == 'ms' ) $lang = 'Malay';
    if( $code == 'ml' ) $lang = 'Malayalam';
    if( $code == 'mt' ) $lang = 'Maltese';
    if( $code == 'mi' ) $lang = 'Maori';
    if( $code == 'mr' ) $lang = 'Marathi';
    if( $code == 'mo' ) $lang = 'Moldavian';
    if( $code == 'mn' ) $lang = 'Mongolian';
    if( $code == 'na' ) $lang = 'Nauru';
    if( $code == 'ne' ) $lang = 'Nepali';
    if( $code == 'no' ) $lang = 'Norwegian';
    if( $code == 'oc' ) $lang = 'Occitan';
    if( $code == 'or' ) $lang = 'Oriya';
    if( $code == 'om' ) $lang = 'Oromo (Afaan Oromo)';
    if( $code == 'ps' ) $lang = 'Pashto (Pushto)';
    if( $code == 'pl' ) $lang = 'Polish';
    if( $code == 'pt' ) $lang = 'Portuguese';
    if( $code == 'pa' ) $lang = 'Punjabi';
    if( $code == 'qu' ) $lang = 'Quechua';
    if( $code == 'rm' ) $lang = 'Rhaeto-Romance';
    if( $code == 'ro' ) $lang = 'Romanian';
    if( $code == 'ru' ) $lang = 'Russian';
    if( $code == 'sm' ) $lang = 'Samoan';
    if( $code == 'sg' ) $lang = 'Sangro';
    if( $code == 'sa' ) $lang = 'Sanskrit';
    if( $code == 'sr' ) $lang = 'Serbian';
    if( $code == 'sh' ) $lang = 'Serbo-Croatian';
    if( $code == 'st' ) $lang = 'Sesotho';
    if( $code == 'tn' ) $lang = 'Setswana';
    if( $code == 'sn' ) $lang = 'Shona';
    if( $code == 'ii' ) $lang = 'Sichuan Yi';
    if( $code == 'sd' ) $lang = 'Sindhi';
    if( $code == 'si' ) $lang = 'Sinhalese';
    if( $code == 'ss' ) $lang = 'Siswati';
    if( $code == 'sk' ) $lang = 'Slovak';
    if( $code == 'sl' ) $lang = 'Slovenian';
    if( $code == 'so' ) $lang = 'Somali';
    if( $code == 'es' ) $lang = 'Spanish';
    if( $code == 'su' ) $lang = 'Sundanese';
    if( $code == 'sw' ) $lang = 'Swahili (Kiswahili)';
    if( $code == 'sv' ) $lang = 'Swedish';
    if( $code == 'tl' ) $lang = 'Tagalog';
    if( $code == 'tg' ) $lang = 'Tajik';
    if( $code == 'ta' ) $lang = 'Tamil';
    if( $code == 'lz' ) $lang = 'Balaji';
    if( $code == 'tt' ) $lang = 'Tatar';
    if( $code == 'te' ) $lang = 'Telugu';
    if( $code == 'th' ) $lang = 'Thai';
    if( $code == 'bo' ) $lang = 'Tibetan';
    if( $code == 'ti' ) $lang = 'Tigrinya';
    if( $code == 'to' ) $lang = 'Tonga';
    if( $code == 'ts' ) $lang = 'Tsonga';
    if( $code == 'tr' ) $lang = 'Turkish';
    if( $code == 'tk' ) $lang = 'Turkmen';
    if( $code == 'tw' ) $lang = 'Twi';
    if( $code == 'ug' ) $lang = 'Uighur';
    if( $code == 'uk' ) $lang = 'Ukrainian';
    if( $code == 'ur' ) $lang = 'Urdu';
    if( $code == 'uz' ) $lang = 'Uzbek';
    if( $code == 'vi' ) $lang = 'Vietnamese';
    if( $code == 'vo' ) $lang = 'Volap�k';
    if( $code == 'wa' ) $lang = 'Wallon';
    if( $code == 'cy' ) $lang = 'Welsh';
    if( $code == 'wo' ) $lang = 'Wolof';
    if( $code == 'xh' ) $lang = 'Xhosa';
    if( $code == 'yi' ) $lang = 'Yiddish';
    if( $code == 'ji' ) $lang = 'Yiddish';
    if( $code == 'yo' ) $lang = 'Yoruba';
    if( $code == 'zu' ) $lang = 'Zulu';
    if( $code == '') $lang = 'Unknown';
    if( $lang == null) $lang = strtoupper($code);
    return $lang;
}


