<?php
namespace wiggum\commons\io;

/**
 * @deprecated
 * 
 *
 */
class FileDownloadHeaders {
	
	private $expires;
	private $cacheControl;
	private $pragma;
	private $contentType;
	private $contentTransferEncoding;
	private $contentLength;
	private $contentDisposition;
	
	/**
	 * 
	 */
	public function __construct() {
		$this->initHeaders();
	}
	
	/**
	 *
	 */
	private function initHeaders() {
		$this->expires = "0";
		$this->cacheControl = "no-cache, must-revalidate";
		$this->contentType = "application/force-download";
		$this->contentTransferEncoding = "Binary";
		$this->contentDisposition = "attachment";
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param boolean $replace [default=true]
	 */
	private function getHeader($name, $value, $replace = true) {
		if(isset($value)) {
			header("{$name}: {$value}", $replace);
		}
	}
	
	/**
	 *
	 */
	public function getHeaders() {
		$this->getHeader('Expires', $this->expires);
		$this->getHeader('Cache-Control', $this->cacheControl);
		$this->getHeader('Content-Type', $this->contentType);
		$this->getHeader('Content-Transfer-Encoding', $this->contentTransferEncoding);
		$this->getHeader('Content-Length', $this->contentLength);
		$this->getHeader('Content-Disposition', $this->contentDisposition);
	}
	
	/**
	 * @param string $expires
	 */
	public function setExpires($expires) {
		$this->expires = $expires;
	}
	
	/**
	 * @return string
	 */
	public function getExpires() {
		return $this->expires;
	}
	
	/**
	 * @param string $cacheControl
	 */
	public function setCacheControl($cacheControl) {
		$this->cacheControl = $cacheControl;
	}
	
	/**
	 * @return string
	 */
	public function getCacheControl() {
		return $this->cacheControl;
	}
	
	/**
	 * @param string $contentType
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}
	
	/**
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}
	
	/**
	 * @param string $contentTransferEncoding
	 */
	public function setContentTransferEncoding($contentTransferEncoding) {
		$this->contentTransferEncoding = $contentTransferEncoding;
	}
	
	/**
	 * @return string
	 */
	public function getContentTransferEncoding() {
		return $this->contentTransferEncoding;
	}
	
	/**
	 * @param string $contentLength
	 */
	public function setContentLength($contentLength) {
		$this->contentLength = $contentLength;
	}
	
	/**
	 * @return string
	 */
	public function getContentLength() {
		return $this->contentLength;
	}
	
	/**
	 * @param string $filename
	 * @param string $disposition [default='attachment']
	 */
	public function setContentDisposition($filename, $disposition = 'attachment') {
		if($disposition != '') {
			$this->contentDisposition = "{$disposition}; filename=\"{$filename}\"";
		} else {
			$this->contentDisposition = NULL;
		}
	}
	
	/**
	 * @return string
	 */
	public function getContentDisposition() {
		return $this->contentDisposition;
	}
	
	/**
	 * @param string $pragma
	 */
	public function setPragma($pragma) {
		$this->pragma = $pragma;
	}
	
	/**
	 * @return string
	 */
	public function getPragma() {
		return $this->pramga;
	}
	
}
?>