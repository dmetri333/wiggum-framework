<?php
namespace wiggum\commons\imagery;

use \Imagick;
use \ImagickDraw;
use \RuntimeException;

class ImageProcessorIM extends ImageProcessor {
	
	/**
	 * 
	 * @param string $dir
	 * @param string $filename
	 * @throws RuntimeException
	 */
	public function __construct($dir, $filename) {
		if(defined('IMAGEMAGICK_DISABLED') && IMAGEMAGICK_DISABLED) {
			throw new RuntimeException('imagick manually disabled');
		}
		if(!extension_loaded('imagick')) {
			throw new RuntimeException('imagick extension not found');
		}
		$this->dir = $dir;
		$this->filename = $filename;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wiggum/commons/imagery/wiggum\commons\imagery.ImageProcessor::createThumb()
	 */
	public function createThumbnail($width, $height, $prefix = 'thumb_') {
		$imagick = new Imagick($this->dir.$this->filename);
		
		$width = min($width, $imagick->getImageWidth());
	 	$height = min($height, $imagick->getImageHeight());
		
		$bestfit = true;
		if($this->defaultDimension == 'width') {
			$height = 0;
			$bestfit = false;
		} else if($this->defaultDimension == 'height') {
			$width = 0;
			$bestfit = false;
		}
		$result = $imagick->thumbnailImage($width, $height, $bestfit);
		
		if($result !== false) {
			$thumbName = $this->createThumbName($this->filename, $prefix);
			$imagick->writeImage($this->dir.$thumbName);
			$imagick->clear();
			$imagick->destroy();
			
			return $thumbName;
		}
		
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see wiggum/commons/imagery/wiggum\commons\imagery.ImageProcessor::createLogoWatermark()
	 */
	public function createLogoWatermark($imagePath, $width, $height, $prefix = 'wm_') {
		$imagick = new Imagick($this->dir.$this->filename);
	 	
	 	$wmImage = new Imagick($imagePath);
	 		
	 	//scale the size of the watermark image to be proportional to the size of the target image
	 	$wmWidth = $imagick->getImageWidth() * 0.4;
	 	$wmHeight = $imagick->getImageHeight() * 0.4;
	 	$wmImage->scaleImage($wmWidth, $wmHeight, true);
	 		
 		//set position of watermark on target image
 		$wmX = $imagick->getImageWidth() - $wmWidth;
 		$wmY = $imagick->getImageHeight() - $wmHeight;
 		
 		$imagick->compositeImage($wmImage, Imagick::COMPOSITE_BUMPMAP, $wmX, $wmY, Imagick::CHANNEL_ALPHA | Imagick::CHANNEL_OPACITY);

	 	//if image width or height is smaller than requested dimensions, then use the source image size
	 	$width = min($width, $imagick->getImageWidth());
	 	$height = min($height, $imagick->getImageHeight());
	 	if($width < 0 && $height < 0) {
	 		$width = $imagick->getImageWidth();
	 		$height = $imagick->getImageHeight();
	 	}
	 	
 		$bestfit = true;
		if ($this->defaultDimension == 'width') {
			$height = 0;
			$bestfit = false;
		} else if($this->defaultDimension == 'height') {
			$width = 0;
			$bestfit = false;
		}
		
		$result = $imagick->thumbnailImage($width, $height, $bestfit);
		if ($result) {
			$thumbName = $this->createThumbName($this->filename, $prefix);
			$imagick->writeImage($this->dir.$thumbName);
			$imagick->clear();
			$imagick->destroy();
		
			return $thumbName;
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wiggum/commons/imagery/wiggum\commons\imagery.ImageProcessor::createTextWatermark()
	 */
	public function createTextWatermark($text, $width, $height, $prefix = 'wm_', $textAngle = -30.0, $textOpacity = 0.4) {
		$imagick = new Imagick($this->dir.$this->filename);
	 	
 	 	$draw = new ImagickDraw();
	 	
	 	//set font size proportional to target image size
	 	$fontSize = min($imagick->getImageWidth(), $imagick->getImageHeight()) * 0.12;
	 	$draw->setFontSize($fontSize);
	 	
	 	//text transparency
	 	$draw->setFillOpacity($textOpacity);
	 	
	 	//write text in specified positions
	 	$draw->setGravity(Imagick::GRAVITY_CENTER);
	 	$imagick->annotateImage($draw, 0, 0, $textAngle, $text);
	 	$draw->setGravity(Imagick::GRAVITY_NORTH);
	 	$imagick->annotateImage($draw, 0, 0, $textAngle, $text);
	 	$draw->setGravity(Imagick::GRAVITY_SOUTH);
	 	
	 	$imagick->annotateImage($draw, 0, 0, $textAngle, $text);
 	
	 	//if image width or height is smaller than requested dimensions, then use the source image size
	 	$width = min($width, $imagick->getImageWidth());
	 	$height = min($height, $imagick->getImageHeight());
	 	if($width < 0 && $height < 0) {
	 		$width = $imagick->getImageWidth();
	 		$height = $imagick->getImageHeight();
	 	}
	 	
 		$bestfit = true;
		if($this->defaultDimension == 'width') {
			$height = 0;
			$bestfit = false;
		} else if($this->defaultDimension == 'height') {
			$width = 0;
			$bestfit = false;
		}
		
		$result = $imagick->thumbnailImage($width, $height, $bestfit);
		if ($result) {
			$thumbName = $this->createThumbName($this->filename, $prefix);
			$imagick->writeImage($this->dir.$thumbName);
			$imagick->clear();
			$imagick->destroy();
		
			return $thumbName;
		}
		
		return false;
	}
	 
}
?>