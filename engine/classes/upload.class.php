<?php

class TUpload {

    const FILE_CHMOD = 0755;

    static $extensions = array('video/mp4' => 'mp4', 'video/mov' => 'mov');
    static $max_weight = 50485760;
    static $errors = array();

    static function _upload_($FILE, $path, $save_name){
        $loaded_path	= $FILE['tmp_name'];
        $loaded_name	= $FILE['name'];
        $loaded_type	= $FILE['type'];

        if(array_key_exists(strtolower($loaded_type), self::$extensions) == true){
            if(filesize($loaded_path) <= self::$max_weight){
                $file_name = $save_name ? basename($loaded_name) : (uniqid('') . '.' . self::$extensions[$loaded_type]);
                $full_path = sprintf('%s%s/%s', ROOT, $path, $file_name);

                if(move_uploaded_file($loaded_path, $full_path) == true){
                    chmod($full_path, self::FILE_CHMOD);
                    return $file_name;
                } else {
                    self::$errors[] = 'Невозможно загрузить файл из ' . $loaded_path . ' в ' . $full_path . '.';
                    unlink($loaded_path);
                    return false;
                }
            } else {
                self::$errors[] = 'Размер файла больше ' . self::$max_weight . ' байт.';
                return false;
            }
        } else {
            self::$errors[] = 'Недопустимый формат файла: ' . $loaded_type . '.';
            return false;
        }
    }

    static function load($name, $path, $save_name = false){
        self::$errors = array();

        if(is_uploaded_file($_FILES[$name]['tmp_name']) == true){
            return self::_upload_($_FILES[$name], $path, $save_name = false);
        } else {
            self::$errors[] = 'Файл не загружен.';
            return false;
        }
    }

}