<?php
    include 'class.thumbhandler.php'; // Class ThumbHandler

    class CropAvatar {
        private $src;
        private $data;
        private $file;
        private $dist;
        private $msg;

        function __construct($src, $data, $file) {
        	$type = "";
			$woo = "boo";
            if (!empty($src)) {
                $this -> setSrc($src);
				$type = $this -> getPathInfo($src['type']);
                $this -> setDist($this -> getPathInfo($src['type']));
            }

            if (!empty($data)) {
                $this -> setData($data);
            }

            if (!empty($file)) {
                $this -> setFile($file, $type);
            }

            if (!empty($this -> src) && !empty($this -> dist) && !empty($this -> data)) {
                $this -> crop($this -> src, $this -> dist, $this -> data);
            } else {
                $this -> dist = "";
            }
        }

        public function setSrc($src) {
            $this -> src = $src;
        }

        public function setData($data) {
            $this -> data = json_decode($data);
        }

        public function setFile($file, $type) {
            if ($file['error'] === 0) {
            	$bite = "ppp";
                $info = $this -> getPathInfo($file['name']);
                $src  = $this -> makeDir('img/upload') . '/' . md5($info['name']) . '.' . $info['type'];

                if (in_array($info['type'], array('jpg','jpeg','gif','png'))) {

                    if (file_exists($src)) {
                        unlink($src);
                    }

                    $result = move_uploaded_file($file['tmp_name'], $src);

                    if ($result) {
                        $this -> src = $src;
                        $this -> setDist($info['type']);
						
						
						
                    } else {
                         $this -> msg = 'File saving failed!';
                    }
                } else {
                    $this -> msg = 'Please upload image file with the following extensions: jpg, png, gif.';
                }
            } else {
                if (empty($this -> src)) {
                    $this -> msg = 'File upload failed! Error code: ' . $file['error'];
                }
            }
        }

        public function setDist($type) {
        	$strFile = '/' . date('YmdHis') . '.' . $type;
            $this -> dist = $this -> makeDir('img/output') . $strFile;
        }

        public function crop($src, $dist, $data) {
        	$outputWidth = 290;
			$outputHeight = 206;
			
            $crop = new ThumbHandler();
            $crop -> setSrcImg($src);
            $crop -> setCutType(2);
            $crop -> setSrcCutPosition($data -> x1, $data -> y1);
            $crop -> setRectangleCut($data -> width, $data -> height);
            $crop -> setImgDisplayQuality(9);
            $crop -> setDstImg($dist);
			
            $crop -> createImg($outputWidth, $outputHeight);
			$strFile = $dist;
			$fnArray = explode(".",$strFile);
			$arrlength = count($fnArray);
			$type = $fnArray[$arrlength-1];
			$img_width = 290;
			$img_height = 206;
        	
			//Retrieve and scale
			switch ($type) {
				case 'jpg': case 'jpeg': {
					$rsr_org = imagecreatefromjpeg($strFile);
					$rsr_scl = imagescale($rsr_org, $img_width, $img_height,  IMG_BICUBIC_FIXED);
					imagejpeg($rsr_scl, $strFile);
					imagedestroy($rsr_org);
					imagedestroy($rsr_scl);
					break;
				}
				case 'png': {
					$rsr_org = imagecreatefrompng($strFile);
					$rsr_scl = imagescale($rsr_org, $img_width, $img_height,  IMG_BICUBIC_FIXED);
					imagepng($rsr_scl, $strFile);
					imagedestroy($rsr_org);
					imagedestroy($rsr_scl);		
					break;
				}
				case 'gif': {
					$rsr_org = imagecreatefromgif($strFile);
					$rsr_scl = imagescale($rsr_org, $img_width, $img_height,  IMG_BICUBIC_FIXED);
					imagegif($rsr_scl, $strFile);
					imagedestroy($rsr_org);
					imagedestroy($rsr_scl);			
					break;
				}
				default:
					break;
			}
			//Kill the uploaded file
			unlink($src);
        }

        public function getPathInfo($path) {
            $info = pathinfo($path);
            $type  = $info['extension'];
            $name  = strtr($info['basename'], '.' . $type, '');

            return array(
                'name' => $name,
                'type' => $type
            );
        }

        public function makeDir($dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777);
            }

            return $dir;
        }

        public function getResult() {
            return !empty($this -> dist) ? $this -> dist : $this -> src;
        }

        public function getMsg() {
            return $this -> msg;
        }
    }

    $crop = new CropAvatar($_POST['avatar_src'], $_POST['avatar_data'], $_FILES['avatar_file']);
    $response = array(
        'state'  => 200,
        'result' => $crop -> getResult(), // src, dist
        'error' => $crop -> getMsg() // msg
    );

    echo json_encode($response);
?>
