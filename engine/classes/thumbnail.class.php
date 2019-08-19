<?php

class TThumbnail{
    private $allow_types;
    private $errors;
    private $use_cache;
    private $chache_dir;

    public function __construct(){
        $this->allow_types = array('image/jpeg', 'image/png', 'image/gif');
        $this->errors = array();
        $this->use_cache = true;
        $this->chache_dir = ROOT . '/uploadfiles/cache/thumbnail';
    }

    public function __desctruct(){}

    // PUBLIC ------------------------------------------------------------------

    public function src($src, $width = 310, $height = 310, $draft = false){
        $path = ROOT . $src;
        if(file_exists($path) && is_file($path)){
            $image_data = getimagesize($path);
            if($image_data[0] > $width || $image_data[1] > $height){
                $params = array($src, $width, $height, $draft);
                return sprintf('/index.php?%s', $this->encode_params($params));
            } else {
                return $src;
            }
        }
    }

    public function out($query){
        if(strlen($query)){
            if($this->use_cache && $this->load_cache_img($query)){
                return;
            } elseif(count($params = $this->decode_params($query)) > 0){
                $path = ROOT . (string)$params[0];
                if(file_exists($path) && is_file($path)){
                    $image_data = getimagesize($path);
                    $img_width	= $image_data[0];
                    $img_height	= $image_data[1];
                    $img_type	= $image_data['mime'];
                    $allow_types = array_map('preg_quote', $this->allow_types);
                    $allow_types = join('|', $allow_types);
                    if(preg_match("#{$allow_types}#i", $img_type) == true){
                        $max_width = isset($params[1]) && intval($params[1]) ? (int)$params[1] : 310;
                        $max_height = isset($params[2]) && intval($params[2]) ? (int)$params[2] : 310;
                        if($img_width <= $max_width && $img_height <= $max_height){
                            header("Content-Type: " . $img_type, true);
                            header("Content-Length: " . filesize($path), true);
                            header("Content-Disposition: attachment; filename=" . basename($path), true);
                            readfile($path);
                        } else {
                            switch ($img_type){
                                case "image/pjpeg":
                                case "image/jpeg":
                                    $image_creat_func = 'imagecreatefromjpeg';
                                    $image_save_func = 'imagejpeg';
                                    $ext = 'jpg';
                                    break;
                                case "image/png":
                                    $image_creat_func = 'imagecreatefrompng';
                                    $image_save_func = 'imagepng';
                                    $ext = 'png';
                                    break;
                                case "image/gif":
                                    $image_creat_func = 'imagecreatefromgif';
                                    $image_save_func = 'imagegif';
                                    $ext = 'gif';
                                    break;
                            }
                            $src_im = $image_creat_func($path);
                            $k = min($max_width / $img_width, $max_height / $img_height);
                            $new_w = round($img_width  * $k);
                            $new_h = round($img_height * $k);
                            $dst_im = imagecreatetruecolor($new_w, $new_h);
                            $bg = imagecolorallocate($dst_im, 255, 255, 255);
                            imagefill($dst_im, 0, 0, $bg);
                            if(key_exists(3, $params) && $params[3] == true){
                                imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $new_w, $new_h, $img_width, $img_height);
                            } else {
                                imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $new_w, $new_h, $img_width, $img_height);
                            }
                            ob_start();
                            if($image_save_func == 'imagejpeg'){
                                imageinterlace($dst_im, 0);
                                $image_save_func($dst_im, null, 90);
                            } else {
                                $image_save_func($dst_im);
                            }
                            $final_image = ob_get_contents();
                            ob_end_clean();
                            imagedestroy($dst_im);
                            imagedestroy($src_im);
                            if($this->use_cache){
                                $this->save_cache_img($query, $final_image, $ext);
                            }
                            header("Content-Type: " . $img_type, true);
                            header("Content-Length: " . strlen($final_image), true);
                            header("Content-Disposition: attachment; filename=" . basename($path), true);
                            print($final_image);
                        }
                    } else {
                        $this->errors[] = 'File type is not allowed';
                    }
                } else {
                    $this->errors[] = 'File not exists';
                }
            } else {
                $this->errors[] = 'Empty params';
            }
        } else {
            $this->errors[] = 'Empty query string';
        }
    }

    public function errors($echo = false){
        if($echo == true && count($this->errors) > 0)
            echo join($this->errors, '<br>');
        else
            return $this->errors;
    }

    // PRIVATE -----------------------------------------------------------------

    private function encode_params($params){
        return (string)base64_encode(serialize($params));
    }

    private function decode_params($query){
        return (array)unserialize(base64_decode($query));
    }

    private function save_cache_img($query, $img, $ext){
        $file = sprintf('%s.%s', md5($query), $ext);
        $path = sprintf('%s/%s', $this->chache_dir, $file);
        if((file_exists($path) && is_file($path)) == false){
            file_put_contents($path, $img);
        }
    }

    private function load_cache_img($query, $ext = 'jpg'){
        $file = sprintf('%s.%s', md5($query), $ext);
        $path = sprintf('%s/%s', $this->chache_dir, $file);
        if(file_exists($path) && is_file($path)){
            $image_data = getimagesize($path);
            header("Content-Type: " . $image_data['mime'], true);
            header("Content-Length: " . filesize($path), true);
            header("Content-Disposition: inline; filename={$file}", true);
            readfile($path);
            return true;
        }
        return false;
    }
}