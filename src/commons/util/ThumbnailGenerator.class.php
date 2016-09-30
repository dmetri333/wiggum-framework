<?php
namespace wiggum\commons\util;

use \Exception;
use \wiggum\commons\imagery\ImageProcessorIM;
use \wiggum\commons\imagery\ImageProcessorGD;

class ThumbnailGenerator {
	
	private $imageProcessor;
	
	/**
	 * 
	 * @param string $path
	 * @param string $filename
	 */
	public function __construct($path, $filename) {
		try {
			$this->imageProcessor = new ImageProcessorIM($path, $filename);
		} catch(Exception $e) {
			error_log($e->getMessage());
			error_log('falling back to gd processor ...');
			$this->imageProcessor = new ImageProcessorGD($path, $filename);
		}
	 }
	 
	 /**
	  * Create a thumbnail in same directory as original image.
	  *
	  * @param integer $width
	  * @param integer $height
	  * @param string $prefix
	  * @return string - filename of created thumbnail.
	  * @throws Exception
	  */
	 public function createThumbnail($width, $height, $prefix = 'thumb_') {
	 	return $this->imageProcessor->createThumbnail($width, $height, $prefix);
	 }
	 
	 /**
	  * 
	  * @param string $imagePath
	  * @param integer $width
	  * @param integer $height
	  * @param string $prefix [default='wm_']
	  * @return string
	  */
	 public function createLogoWatermark($imagePath, $width, $height, $prefix = 'wm_') {
	 	return $this->imageProcessor->createLogoWatermark($imagePath, $width, $height, $prefix);
	 }

	/**
	 * 
	 * @param string $text
	 * @param int $width
	 * @param int $height
	 * @param string $prefix [default='wm_']
	 * @param int $textAngle [default=-30.0]
	 * @param int $textOpacity [default=0.4]
	 * @return string
	 */
	 public function createTextWatermark($text, $width, $height, $prefix = 'wm_', $textAngle = -30.0, $textOpacity = 0.4) {
	 	return $this->imageProcessor->createTextWatermark($text, $width, $height, $prefix, $textAngle, $textOpacity);
	 }
	 
	 /**
	  * 
	  * @return string
	  */
	 public function getSpaceReplace() {
	 	return $this->imageProcessor->getSpaceReplace();
	 }
	 
	 /**
	  * 
	  * @param string $spaceReplace
	  */
	 public function setSpaceReplace($spaceReplace) {
	 	$this->imageProcessor->setSpaceReplace($spaceReplace);
	 }
	 
	 /**
	  * 
	  * @return string
	  */
	 public function getDefaultDimension() {
	 	return $this->imageProcessor->getDefaultDimension();
	 }
	 
	 /**
	  * 
	  * @param string $defaultDimension
	  */
	 public function setDefaultDimension($defaultDimension) {
	 	$this->imageProcessor->setDefaultDimension($defaultDimension);
	 }
	 
}
?>