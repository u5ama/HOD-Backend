<?php

namespace Modules\Business\Models;

use Illuminate\Database\Eloquent\Model;

class Domains extends Model{

    protected $table = 'domains_data';

    protected $guarded = [];

    public $timestamps = false;

//     protected $fillable = [
//         'website', 'business_id', 'mobile_ready', 'page_speed_score', 'title_tag', 'mobile_ready_score', 'page_speed_suggestion', 'mobile_ready_suggestion', 'google_analytics'
//     ];
//     protected $fillable = [
//         'domain', 'date', 'meta_data', 'headings', 'image_alt', 'keywords_cloud', 'ratio_data', 'gzip', 'resolve', 'ip_can', 'links_analyser', 'broken_links', 'robots', 'sitemap', 'embedded', 'iframe', 'whois', 'mobile_fri', 'mobile_com', '404_page', 'load_time', 'domain_typo', 'email_privacy', ''
//     ];
//    protected $primaryKey = 'website_id';
//
//    public function webmaster()
//    {
//        return $this->belongsTo(Business::class);
//    }


}
