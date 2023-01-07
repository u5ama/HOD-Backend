<?php
return [
    //Environment=> test/production
    'env' => 'production',
    //Google Ads
    'production' => [
        'developerToken' => "4lFrHuGLA6bw2hlSUv7-sQ",
        'clientCustomerId' => "719-438-7844",
        'userAgent' => "Heroes of Digital (SG Ads)",
        'clientId' => "387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com",
        'clientSecret' => "TAIIgMZqPucrc6ZSppPhhQO6",
        'refreshToken' => "ya29.a0AfH6SMC4Y2MM4aswnc1cm1e0x40r4ZuTDETGwb1yC_AA8IiSYaMGqX0R_f7SFLmdwKZi9EAEyewomWy4SQUY48Adi1WbswVQHSAKoPKIvyogC-8Kc0dsmeBV2MzrqBvJ5qYMxH8PVJIehBOD-qxEtIlHrQJOzyZji34"
    ],
    'test' => [
        'developerToken' => "4lFrHuGLA6bw2hlSUv7-sQ",
        'clientCustomerId' => "719-438-7844",
        'userAgent' => "Heroes of Digital (SG Ads)",
        'clientId' => "387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com",
        'clientSecret' => "TAIIgMZqPucrc6ZSppPhhQO6",
        'refreshToken' => "ya29.a0AfH6SMC4Y2MM4aswnc1cm1e0x40r4ZuTDETGwb1yC_AA8IiSYaMGqX0R_f7SFLmdwKZi9EAEyewomWy4SQUY48Adi1WbswVQHSAKoPKIvyogC-8Kc0dsmeBV2MzrqBvJ5qYMxH8PVJIehBOD-qxEtIlHrQJOzyZji34"
    ],
    'oAuth2' => [
        'authorizationUri' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'redirectUri' => 'urn:ietf:wg:oauth:2.0:oob',
        'tokenCredentialUri' => 'https://www.googleapis.com/oauth2/v4/token',
        'scope' => 'https://www.googleapis.com/auth/adwords'
    ]
];
