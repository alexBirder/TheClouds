<?php

class TImager{
	const FILE_CHMOD		= 0755;
	const JPEG_QUALITY		= 100;
	const JPEG_INTERLACE	= 0;

	static $extensions = array('image/pjpeg' => 'jpg', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif');
	static $max_weight = 10485760; // 10 Mb
	static $errors = array();

	static function _upload_($FILE, $path, $save_name){ // private method !!!
		$size = getimagesize($FILE['tmp_name']);

		$loaded_path	= $FILE['tmp_name'];
		$loaded_name	= $FILE['name'];
		$loaded_width	= $size[0];
		$loaded_height	= $size[1];
		$loaded_type	= $size['mime'];

		if(array_key_exists(strtolower($loaded_type), self::$extensions) == true){
			if(filesize($loaded_path) <= self::$max_weight){
				$file_name = $save_name ? basename($loaded_name) : (uniqid('') . '.' . self::$extensions[$loaded_type]);
				$full_path = sprintf('%s%s/%s', ROOT, $path, $file_name);

				if(move_uploaded_file($loaded_path, $full_path) == true){
					chmod($full_path, self::FILE_CHMOD);
					return $file_name;
				}
				else{
					self::$errors[] = 'Невозможно загрузить файл из ' . $loaded_path . ' в ' . $full_path . '.';
					unlink($loaded_path);
					return false;
				}
			}
			else{
				self::$errors[] = 'Размер файла больше ' . self::$max_weight . ' байт.';
				return false;
			}
		}
		else{
			self::$errors[] = 'Недопустимый формат файла: ' . $loaded_type . '.';
			return false;
		}
	}

	static function load($name, $path, $save_name = false){
		self::$errors = array();

		if(is_uploaded_file($_FILES[$name]['tmp_name']) == true){
			return self::_upload_($_FILES[$name], $path, $save_name = false);
		}
		else{
			self::$errors[] = 'Файл не загружен.';
			return false;
		}
	}

	static function load_multiple($name, $path, $save_name = false){
		self::$errors = array();
		$uploaded_files = array();

		if(array_key_exists($name, $_FILES) && sizeof($_FILES[$name] > 0)){
			$FILES = self::diverse_array($_FILES[$name]);
			foreach($FILES as $FILE){
				if(is_uploaded_file($FILE['tmp_name']) == true){
					$uploaded_files[] = self::_upload_($FILE, $path, $save_name = false);
				}
			}
		}
		else{
			self::$errors[] = 'Файлы не загружены.';
		}

		return $uploaded_files;
	}

	static function copy_external($file, $path){
		self::$errors = array();

		$loaded_name = basename($file);
	    $path_info = pathinfo($loaded_name);
		$ext = $path_info['extension'];

		do{
			$file_name = uniqid('') . '.' . $ext;
			$full_path_to = sprintf('%s%s/%s', ROOT, $path, $file_name);
		} while(file_exists($full_path_to));

		if(copy($file, $full_path_to) == true){
			chmod($full_path_to, self::FILE_CHMOD);
			return $file_name;
		}
		else{
			self::$errors[] = "Невозможно скопировать файл из {$full_path_from} в {$full_path_to}.";
		}
	}

	static function duplicate($file, $path){
		self::$errors = array();
		$full_path_from = sprintf('%s%s', ROOT, $file);

		if(file_exists($full_path_from) == true && is_file($full_path_from) == true){
			$loaded_name = basename($full_path_from);
		    $path_info = pathinfo($loaded_name);
			$ext = $path_info['extension'];

			do{
				$file_name = uniqid('') . '.' . $ext;
				$full_path_to = sprintf('%s%s/%s', ROOT, $path, $file_name);
			} while(file_exists($full_path_to));

			if(copy($full_path_from, $full_path_to) == true){
				chmod($full_path_to, self::FILE_CHMOD);
				return $file_name;
			}
			else{
				self::$errors[] = "Невозможно скопировать файл из {$full_path_from} в {$full_path_to}.";
			}
		}

		return false;
	}

	static function load_resize($name, $path, $params, $save_name = false){
		self::$errors = array();
		$file_name = self::load($name, $path, $save_name);

		if($file_name){
			$new_path = sprintf('%s/%s', $path, $file_name);
	        self::resize($new_path, $params);
		}

		return $file_name;
	}

	static function copy_resize($file, $path, $params, $save_name = false){
		self::$errors = array();

		$path_full = sprintf('%s%s', ROOT, $file);

		if(file_exists($path_full) == true && is_file($path_full) == true){
			$duplicate = TImager::duplicate($file, $path);
			if($duplicate){
				$duplicate_path = sprintf('%s/%s', $path, $duplicate);
				$duplicate_path_full = sprintf('%s/%s', $path, $duplicate);
				if(self::resize($duplicate_path, $params)){
					return $duplicate;
				}
				else{
					unlink($duplicate_path_full);
					self::$errors[] = 'Невозможно сжать файл ' . $duplicate_path . '.';
					return false;
				}
			}
			else{
				self::$errors[] = 'Невозможно загрузить файл.';
				return false;
			}
		}
		else{
			self::$errors[] = 'Файл не существует ' . $path_full . '.';
			return false;
		}

		return $file_to;
	}

	static function resize($path, $params){
		self::$errors = array();
		$full_path = sprintf('%s%s', ROOT, $path);

		if(file_exists($full_path) == true && is_file($full_path) == true){
			$size = getimagesize($full_path);

			$loaded_width	= $size[0];
			$loaded_height	= $size[1];
			$loaded_type	= $size['mime'];

		    switch ($loaded_type){
		        case "image/pjpeg":
		        case "image/jpeg":
					$image_creat_func = 'imagecreatefromjpeg';
					$image_save_func = 'imagejpeg';
					break;
		        case "image/png":
					$image_creat_func = 'imagecreatefrompng';
					$image_save_func = 'imagepng';
					break;
		        case "image/gif":
					$image_creat_func = 'imagecreatefromgif';
					$image_save_func = 'imagegif';
					break;
			}

			if($loaded_width > $params['width'] || $loaded_height > $params['height']){
				$src_im = $image_creat_func($full_path);

				if($loaded_width - $params['width'] > $loaded_height - $params['height']){
					$new_w = $params['width'];
					$new_h = floor($new_w * $loaded_height / $loaded_width);
				}
				else{
					$new_h = $params['height'];
					$new_w = floor($new_h * $loaded_width / $loaded_height);
    			}

				$dst_im = imagecreatetruecolor($new_w, $new_h);
				imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $new_w, $new_h, $loaded_width, $loaded_height);

				unlink($full_path);
	        	if($image_save_func == 'imagejpeg'){
					imageinterlace($dst_im, self::JPEG_INTERLACE);
					$image_save_func($dst_im, $full_path, self::JPEG_QUALITY);
				} else { $image_save_func($dst_im, $full_path); }
				chmod($full_path, self::FILE_CHMOD);

				imagedestroy($dst_im);
				imagedestroy($src_im);
			}

			return true;
		}
		else{
			self::$errors[] = 'Файл не существует ' . $full_path . '.';
			return false;
		}
	}

	static function crop_resize($path, $params){
		self::$errors = array();
		$full_path = sprintf('%s%s', ROOT, $path);

		if(file_exists($full_path) == true && is_file($full_path) == true){
			$size = getimagesize($full_path);

			$loaded_width	= $size[0];
			$loaded_height	= $size[1];
			$loaded_type	= $size['mime'];

		    switch ($loaded_type){
		        case "image/pjpeg":
		        case "image/jpeg":
					$image_creat_func = 'imagecreatefromjpeg';
					$image_save_func = 'imagejpeg';
					break;
		        case "image/png":
					$image_creat_func = 'imagecreatefrompng';
					$image_save_func = 'imagepng';
					break;
		        case "image/gif":
					$image_creat_func = 'imagecreatefromgif';
					$image_save_func = 'imagegif';
					break;
			}

			if(round($loaded_width / $loaded_height, 2) != round($params['width'] / $params['height'], 2)){ // cropping and resizing image
				$ratio_original = $loaded_width / $loaded_height;
				$ratio_cropped = $params['width'] / $params['height'];

				if($ratio_cropped > $ratio_original){ // crop vertical
					$cropWidth = $loaded_width;
					$cropHeight = $loaded_width * $params['height'] / $params['width'];
				}
				else{ // crop horizontal
					$cropWidth = $params['width'] * $loaded_height / $params['height'];
					$cropHeight = $loaded_height;
				}

				$centreX = round($loaded_width / 2);
				$centreY = round($loaded_height / 2);

				$cropWidthHalf = round($cropWidth / 2);
				$cropHeightHalf = round($cropHeight / 2);

				$x1 = max(0, $centreX - $cropWidthHalf);
				$y1 = max(0, $centreY - $cropHeightHalf);
				$x2 = min($loaded_width, $centreX + $cropWidthHalf);
				$y2 = min($loaded_height, $centreY + $cropHeightHalf);

				$width = $x2 - $x1;
				$height = $y2 - $y1;

				$src_im = $image_creat_func($full_path);
				$dst_im = imagecreatetruecolor(round($cropWidth), round($cropHeight));
				imagecopy($dst_im, $src_im, 0, 0, $x1, $y1, $width, $height);

				if($cropWidth > $params['width'] || $cropHeight > $params['height']){
					if($cropWidth - $params['width'] > $cropHeight - $params['height']){
						$new_w = $params['width'];
						$new_h = floor($new_w * $cropHeight / $cropWidth);
					}
					else{
						$new_h = $params['height'];
						$new_w = floor($new_h * $cropWidth / $cropHeight);
	    			}
					$src_im = $dst_im;
					$dst_im = imagecreatetruecolor($new_w, $new_h);
					imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $new_w, $new_h, $cropWidth, $cropHeight);
				}

				@unlink($full_path);
	        	if($image_save_func == 'imagejpeg'){
					imageinterlace($dst_im, self::JPEG_INTERLACE);
					$image_save_func($dst_im, $full_path, self::JPEG_QUALITY);
				} else { $image_save_func($dst_im, $full_path); }
				chmod($full_path, self::FILE_CHMOD);

				imagedestroy($dst_im);
				imagedestroy($src_im);
			}
			elseif($loaded_width > $params['width'] || $loaded_height > $params['height']){ // luck, just resize image
				self::resize($path, $params);
			}

			return true;
		}
		else{
			self::$errors[] = 'Файл не существует ' . $full_path . '.';
			return false;
		}
	}

	static function diverse_array($vector){
		$result = array();
		foreach($vector as $key1 => $value1)
			foreach($value1 as $key2 => $value2)
				$result[$key2][$key1] = $value2;
		return $result;
	}
}

?>
