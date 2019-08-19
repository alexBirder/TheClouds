<?php

$overload_enable = true;
$overload_debug = false;

if ($overload_enable && isset($_SERVER['REMOTE_ADDR'])) {
    $not_rated_as = '13238,15169,8075,10310,36647,13335,2635,32934,38365,55967,16509,2559,19500,47764,17012,1449,43247,32734,15768,33512,18730,30148';
    $remote_ip = $_SERVER['REMOTE_ADDR'];

    $secure_cookie_label = 'ct_anti_ddos_key';
    $secure_cookie_salt = '4xU9mn2X7iPZpeW2';
    $secure_cookie_key = md5($remote_ip . ':' . $secure_cookie_salt);
    $secure_cookie_days = 180;
    $redirect_delay = 3;

    $test_ip = true;
    $set_secure_cookie = true;

    if(isset($_COOKIE[$secure_cookie_label]) && $_COOKIE[$secure_cookie_label] == $secure_cookie_key) {
        $test_ip = false;
        $set_secure_cookie = false;
    }

    $skip_trusted = false;

    if($test_ip && function_exists('geoip_org_by_name')) {
        $visitor_org = geoip_org_by_name($remote_ip);
        if ($visitor_org !== false && preg_match("/^AS(\d+)\s/", $visitor_org, $matches)) {
            $not_rated_as = explode(",", $not_rated_as);
            foreach ($not_rated_as as $asn) {
                if($skip_trusted) { continue; }
                if($asn == $matches[1]) { $skip_trusted = true; }
            }
            if($skip_trusted) { $test_ip = false; }
        }
    }

    if($set_secure_cookie) {
        setcookie($secure_cookie_label, $secure_cookie_key, null, '/');
    }
}
