<?php

class THistory {

    static function history_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    static function send($message = ''){
        global $DB;

        $global_data = array(
            'ip' => self::history_ip(),
            'user' => $_COOKIE['name'],
            'action' => htmlspecialchars($message)
        );
        $DB->insert('settings_history', $global_data);
    }

}

