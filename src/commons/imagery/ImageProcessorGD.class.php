<?php
namespace wiggum\commons\imagery;

use \BadFunctionCallException;
use \RuntimeException;
use \Exception;
use \wiggum\commons\logging\Logger;

class ImageProcessorGD extends ImageProcessor {
	
	/**
	 * 
	 * @param string $dir
	 * @param string $filename
	 * @throws BadFunctionCallException
	 */
	public function __construct($dir, $filename) {
		if(!function_exists('exif_imagetype')) {
	 		throw new BadFunctionCallException('Function not found: exif_imagetype.\n configure PHP with --enable-exif.');
	 	}
		
		$this->dir = $dir;
		$this->filename = $filename;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wiggum/commons/imagery/wiggum\commons\imagery.ImageProcessor::createThumbnail()
	 */
	public function createThumbnail($width, $height, $prefix = 'thumb_') {
		//imagecreatefromjpeg may run out of memory and cause a fatal error, check if image will fit in memory before processing
		$this->checkMemory();
		
		//determine image type
	 	$imagetype = exif_imagetype($this->dir . $this->filename);
	 	if($imagetype == IMAGETYPE_JPEG) {
	 		$srcImg = imagecreatefromjpeg($this->dir . $this->filename);	
	 	} else if($imagetype == IMAGETYPE_GIF) {
	 		$srcImg = imagecreatefromgif($this->dir . $this->filename);
	 	} else if($imagetype == IMAGETYPE_PNG) {
	 		$srcImg = imagecreatefrompng($this->dir . $this->filename);
	 	} else {
	 		throw new Exception('Unsupported imagetype code ' . $imagetype);
	 	}
	 	
	 	//determine thumbnail size keeping aspect ratio
	 	$old_x=imageSX($srcImg);
		$old_y=imageSY($srcImg);
		
		if ($height > $old_y) {
			$height = $old_y;
			$width = $old_y;
		}
		if ($width > $old_x) {
			$width = $old_x;
			$height = $old_x;
		}
		
		if ($this->defaultDimension == 'height') {
			$thumb_w = $old_x*($width/$old_y);
			$thumb_h = $height;
		} else if ($this->defaultDimension == 'width') {
			$thumb_w = $width;
			$thumb_h = $old_y*($height/$old_x);
		} else {
			if ($old_x > $old_y) {
				$thumb_w = $width;
				$thumb_h = $old_y*($height/$old_x);
			} else if ($old_x < $old_y) {
				$thumb_w = $old_x*($width/$old_y);
				$thumb_h = $height;
			} else if ($old_x == $old_y) {
				$thumb_w = $width;
				$thumb_h = $height;
			}
		}
		
		//create image resource
		if($imagetype != IMAGETYPE_GIF) {
			$dstImg = ImageCreateTrueColor($thumb_w, $thumb_h);
		} else {
			$dstImg = imagecreate($thumb_w, $thumb_h);
		}
		
		//preserve transparency for png
		if($imagetype == IMAGETYPE_PNG) {
			imagealphablending($dstImg, false);
			imagesavealpha($dstImg, true);
		} else if($imagetype == IMAGETYPE_GIF) {
			imagecolorallocate($dstImg, 255, 255, 255);
		}
		
		imagecopyresampled($dstImg,$srcImg,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
		
		$thumbName = $this->createThumbName($this->filename, $prefix);
		
		if($imagetype == IMAGETYPE_PNG) {
			imagepng($dstImg, $this->dir . $thumbName, 4);
		} else if($imagetype == IMAGETYPE_GIF) {
			imagegif($dstImg, $this->dir . $thumbName);	 
		} else {
			imagejpeg($dstImg, $this->dir . $thumbName, 100);
		}	
		Logger::info("created thumbnail {$thumbName}", __METHOD__);
		
		imagedestroy($dstImg);
		imagedestroy($srcImg);
		
		return $thumbName;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wiggum/commons/imagery/wiggum\commons\imagery.ImageProcessor::createLogoWatermark()
	 */
	public function createLogoWatermark($imagePath, $width, $height, $prefix = 'wm_') {
		Logger::warning('gd watermark not supported', __METHOD__);
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wiggum/commons/imagery/wiggum\commons\imagery.ImageProcessor::createTextWatermark()
	 */
	public function createTextWatermark($text, $width, $height, $prefix = 'wm_', $textAngle = -30.0, $textOpacity = 0.4) {
		Logger::warning('gd watermark not supported', __METHOD__);
		return false;
	}
	
	/**
	 * Check if the image will fit in memory. 
	 * GD stores the image uncompressed in memory for processing.
	 * We can check memory required by checking the width * height * bytes per pixel
	 * 
	 * @throws RuntimeException
	 * @return boolean
	 */
	private function checkMemory() {
		list($imgWidth, $imgHeight) = getimagesize($this->dir . $this->filename);
		$imgMemory = $imgWidth * $imgHeight * parent::BYTES_PER_PIXEL;
		$memoryLimit = ini_get('memory_limit');
		//FIXME dont assume memorylimit is in mb
		$availableMemory = (int)$memoryLimit * parent::BYTES_PER_MB;

		if($imgMemory > $availableMemory) {
			throw new RuntimeException("Image size exceeds memory limit: {$imgWidth}x{$imgHeight} = {$imgMemory} bytes, {$availableMemory} bytes available");
		}
		
		return true;
	}
}
?>