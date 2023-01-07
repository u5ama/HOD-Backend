<?php

namespace Modules\ThirdParty\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\ThirdParty\Models\SysIssue;

class ThirdPartyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SysIssue::create([
            'issue_id'=>'1',
            'title'=>'Missing website',
            'site'=>'',
            'module'=>'Website'
        ]);
        SysIssue::create([
            'issue_id'=>'2',
            'title'=>'Not listed on tripadvisor',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'3',
            'title'=>'Not listed on Google Places',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'4',
            'title'=>'Incorrect Phone in Trip Advisor',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'5',
            'title'=>'Incorrect Address in Trip Advisor',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'6',
            'title'=>'Incorrect website in Trip Advisor',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'7',
            'title'=>'Incorrect phone in Google Places',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'8',
            'title'=>'Incorrect address in Google Places',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'9',
            'title'=>'Incorrect website in Google Places',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'10',
            'title'=>'Need to respond to Google Place Reviews',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'11',
            'title'=>'Need to respond to Tripadvisor Reviews',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'12',
            'title'=>'Not listed on Yelp',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'13',
            'title'=>'Incorrect phone on Yelp',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'14',
            'title'=>'Incorrect address on Yelp',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'15',
            'title'=>'Incorrect website on Yelp',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'16',
            'title'=>'Need to respond to Yelp Reviews',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'17',
            'title'=>'Not listed on Facebook',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);

        SysIssue::create([
            'issue_id'=>'18',
            'title'=>'Incorrect phone on Facebook',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);

        SysIssue::create([
            'issue_id'=>'19',
            'title'=>'Incorrect address on Facebook',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'20',
            'title'=>'Incorrect website on Facebook',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'21',
            'title'=>'Need to respond to Facebook Reviews',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'22',
            'title'=>'Missing profile photo on Facebook',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'23',
            'title'=>'Missing cover photo on Facebook',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'24',
            'title'=>'Not listed on Google Plus',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'25',
            'title'=>'Incorrect phone on Google Plus',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'26',
            'title'=>'Incorrect address on Google Plus',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'27',
            'title'=>'Incorrect website on Google Plus',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'28',
            'title'=>'Need to respond to Google Plus Reviews',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'29',
            'title'=>'Missing profile picture on Google Plus',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'30',
            'title'=>'Missing cover photo on Google Plus',
            'site'=>'Google Plus',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'31',
            'title'=>'Not listed on Angies List',
            'site'=>'Angies List',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'32',
            'title'=>'Incorrect phone on Angies List',
            'site'=>'Angies List',
            'module'=>'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id'=>'33',
            'title'=>'Incorrect address on Angies List',
            'site'=>'Angies List',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'34',
            'title'=>'Incorrect website on Angies List',
            'site'=>'Angies List',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'35',
            'title'=>'Need to respond to Angie\'s List Reviews',
            'site'=>'Angies List',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'36',
            'title'=>'Website has no title tags',
            'site'=>'Website',
            'module'=>'Website'
        ]);
        SysIssue::create([
            'issue_id'=>'37',
            'title'=>'Website does not support mobile phones',
            'site'=>'Website',
            'module'=>'Website'
        ]);
        SysIssue::create([
            'issue_id'=>'38',
            'title'=>'"Website speed not optimized',
            'site'=>'Website',
            'module'=>'Website'
        ]);
        SysIssue::create([
            'issue_id'=>'39',
            'title'=>'Website has no Google Analytics installed',
            'site'=>'Website',
            'module'=>'Website'
        ]);

        SysIssue::create([
            'issue_id'=>'40',
            'title'=>'Phone number not found',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'41',
            'title'=>'Website not found',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'42',
            'title'=>'Address not found',
            'site'=>'Tripadvisor',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'43',
            'title'=>'Phone number not found',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'44',
            'title'=>'Website not found',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'45',
            'title'=>'Address not found',
            'site'=>'Yelp',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'46',
            'title'=>'Phone number not found',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'47',
            'title'=>'Website not found',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'48',
            'title'=>'Address not found',
            'site'=>'Google Places',
            'module'=>'Local Marketing'
        ]);

        SysIssue::create([
            'issue_id'=>'49',
            'title'=>'Phone number not found',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);

        SysIssue::create([
            'issue_id'=>'50',
            'title'=>'Website not found',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);

        SysIssue::create([
            'issue_id'=>'51',
            'title'=>'Address not found',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);

        SysIssue::create([
            'issue_id'=>'52',
            'title'=>'Facebook page not boosting',
            'site'=>'Facebook',
            'module'=>'Social Media'
        ]);
        SysIssue::create([
            'issue_id'=>'53',
            'title'=>'Citation Listing',
            'site'=>'system',
            'module'=>'system'
        ]);
        SysIssue::create([
            'issue_id' => 60,
            'title' => 'Low customer rating',
            'site' => 'Facebook',
            'module' => 'Social Media'
        ]);
        SysIssue::create([
            'issue_id' => 61,
            'title' => 'Low customer rating',
            'site' => 'Tripadvisor',
            'module' => 'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id' => 62,
            'title' => 'Low customer rating',
            'site' => 'Yelp',
            'module' => 'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id' => 63,
            'title' => 'Low customer rating',
            'site' => 'Google Places',
            'module' => 'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id' => 64,
            'title' => 'Low review count',
            'site' => 'Facebook',
            'module' => 'Social Media'
        ]);
        SysIssue::create([
            'issue_id' => 65,
            'title' => 'Low review count',
            'site' => 'Tripadvisor',
            'module' => 'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id' => 66,
            'title' => 'Low review count',
            'site' => 'Yelp',
            'module' => 'Local Marketing'
        ]);
        SysIssue::create([
            'issue_id' => 67,
            'title' => 'Low review count',
            'site' => 'Google Places',
            'module' => 'Local Marketing'
        ]);
    }
}
