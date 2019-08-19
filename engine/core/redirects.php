<?php

$redirects = array();
$redirects = array_change_key_case($redirects, CASE_LOWER);
$current = strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

if(array_key_exists($current, $redirects)){
    redirect($redirects[$current]);
    exit;
}