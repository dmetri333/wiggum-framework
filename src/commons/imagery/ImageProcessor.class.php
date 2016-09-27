<?php
namespace wiggum\commons\imagery;

abstract class ImageProcessor {
	
	const BYTES_PER_PIXEL = 4;
	const BYTES_PER_MB = 1048576;
	
	protected $dir;
	protected $filename;
	protected $spaceReplace = '-';
	protected $defaultDimension;
	
	/**
	 * 
	 * @param string $dir
	 * @param string $filename
	 */
	abstract public function __construct($dir, $filename);
	
	/**
	 * 
	 * @param int $width
	 * @param int $height
	 * @param string $prefix
	 * @return string
	 */
	abstract public function createThumbnail($width, $height, $prefix = 'thumb_');
	
	/**
	 * 
	 * @param string $imagePath
	 * @param int $width
	 * @param int $height
	 * @param string $prefix [default='wm_']
	 * @return string
	 */
	abstract public function createLogoWatermark($imagePath, $width, $height, $prefix = 'wm_');
	
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
	abstract public function createTextWatermark($text, $width, $height, $prefix = 'wm_', $textAngle = -30.0, $textOpacity = 0.4);
	
	/**
	 * 
	 * @param string $filename
	 * @param string $prefix
	 * @return string
	 */
	public function createThumbName($filename, $prefix) {
		$info = pathinfo($filename);
	 	$ext = $info['extension'];
	 	$base = str_replace(' ', $this->spaceReplace, $info['filename']);
	 	$thumbName = "{$prefix}{$base}.{$ext}";
	 	return $thumbName;
	}
	
	/**
	 * 
	 * @param string $spaceReplace
	 */
	public function setSpaceReplace($spaceReplace) {
		$this->spaceReplace = $spaceReplace;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getSpaceReplace() {
		return $this->spaceReplace;
	}
	
	/**
	 * 
	 * @param string $defaultDimension
	 */
	public function setDefaultDimension($defaultDimension) {
		$this->defaultDimension = $defaultDimension;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getDefaultDimension() {
		return $this->defaultDimension;
	}
	
}
?>