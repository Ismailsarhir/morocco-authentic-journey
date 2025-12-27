<?php

namespace Scripts\Common;

class RW_User_Social_Media_Utils {

    public static $platforms = [
        'facebook' => [
            'domaine' => 'facebook.com',
            'usernamePattern' => '/^(profile\.php\?id=\d+|[a-zA-Z0-9._-]+\/?(\?.*)?)$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?facebook\.com\/(profile\.php\?id=\d+|[a-zA-Z0-9._-]+\/?(\?.*)?)$/',
        ],
        'instagram' => [
            'domaine' => 'instagram.com',
            'usernamePattern' => '/^[a-zA-Z0-9._-]+\/?(\?.*)?$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?instagram\.com\/([a-zA-Z0-9._-]+)\/?(\?.*)?$/',
        ],
        'linkedin' => [
            'domaine' => 'linkedin.com',
            'usernamePattern' => '/^in\/[a-zA-Z0-9-]+\/?(\?.*)?$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?linkedin\.com\/in\/([a-zA-Z0-9-]+)\/?(\?.*)?$/',
        ],
        'twitter' => [
            'domaine' => 'twitter.com',
            'usernamePattern' => '/^@?[a-zA-Z0-9_-]+\/?(\?.*)?$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?(twitter\.com|x\.com)\/(@)?([a-zA-Z0-9_-]+)\/?(\?.*)?$/',
        ],
        'mastodon' => [
            'domaine' => 'mastodon.social',
            'usernamePattern' => '/^@[a-zA-Z0-9_]+\/?(\?.*)?$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?mastodon\.social\/@([a-zA-Z0-9_]+)\/?(\?.*)?$/',
        ],
        'youtube' => [
            'domaine' => 'youtube.com',
            'usernamePattern' => '/^(channel\/[a-zA-Z0-9_-]+|user\/[a-zA-Z0-9_-]+|c\/[a-zA-Z0-9_-]+|@[a-zA-Z0-9_-]+)\/?(\?.*)?$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?youtube\.com\/(?:channel\/|user\/|c\/|@)([a-zA-Z0-9_-]+)\/?(\?.*)?$/',
        ],
        'pinterest' => [
            'domaine' => 'pinterest.com',
            'usernamePattern' => '/^[a-zA-Z0-9_\/-]+\/?(\?.*)?$/',
            'urlPattern' => '/^http(s)?:\/\/([a-zA-Z0-9-]+\.)?pinterest\.com\/([a-zA-Z0-9_\/-]+)\/?(\?.*)?$/',
        ]
    ];
    
    static function addHttpsToUrl($url) {
        if (!preg_match('/^http(s)?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        return $url;
    }

    static function validateAndFixUrl($platform, $inputUrl) {
        if(filter_var($inputUrl, FILTER_VALIDATE_URL)) {
            return $inputUrl;
        }

        if(!isset(self::$platforms[$platform])) {
            return false;
        }

        $httpsUrl = self::addHttpsToUrl($inputUrl);
        if(self::isUrlValid($platform, $httpsUrl)) {
            return $httpsUrl;
        }

        $inputUrl = self::cleanUsername($inputUrl, $platform);

        if(!self::isValidUsername($inputUrl, $platform)) {
            return false;
        }

        $fullUrl = self::addHttpsToUrl(self::$platforms[$platform]['domaine'] . '/' . $inputUrl);
        if(self::isUrlValid($platform, $fullUrl)) {
            return $fullUrl;
        }

        return false;
    }
    
    static function isValidUsername($username, $platform) {
        if(strpos($username, self::$platforms[$platform]['domaine']) !== false) {
            return false;
        }

        return preg_match(self::$platforms[$platform]['usernamePattern'], $username);
    }

    static function cleanUsername($username, $platform) {
        $username = trim($username, '/');

        if(in_array($platform, ['mastodon', 'youtube'])) {
            if($platform == 'youtube') {
                if(strpos($username, 'channel/') !== false ||  
                   strpos($username, 'user/') !== false ||  
                   strpos($username, 'c/') !== false) {
                    return $username;
                }
            }
            if(strpos($username, '@') === false) {
                $username = '@' . $username;
            }
        }else if(in_array($platform, ['linkedin'])) {
            if(strpos($username, 'in/') === false) {
                $username = 'in/' . $username;
            }
        }

        return $username;
    }

    static function isUrlValid($platform, $url){
        return preg_match(self::$platforms[$platform]['urlPattern'], $url);	   
    }
}