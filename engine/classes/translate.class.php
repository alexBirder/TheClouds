<?php

class TTranslate {

    static $modules = array();

    static $words = array(
        'Новости' => array('ua' => 'Новини', 'en' => 'News'),
        'Главная' => array('ua' => 'Головна', 'en' => 'Main'),
    );

    static function str($mod, $str){
        $mod = trim(strtolower($mod)); $str = trim(strtolower($str));
        if(array_key_exists($mod, self::$modules) && key_exists($str, self::$modules[$mod])){
            $str = self::$modules[$mod][$str];
        } else {
            $str = '-- undefined message string --';
        }
        return $str;
    }

    static function get($str, $lang){
        $key = trim($str);
        if(isset(self::$words[$key]) && preg_match('/ru|ua|en/i', $lang) && isset(self::$words[$key][$lang])){
            $str = self::$words[$key][$lang];
        }
        return $str;
    }

}