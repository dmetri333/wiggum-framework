<?php
namespace wiggum\exceptions;

use \Exception;

class PageNotFoundException extends Exception {
	
	/**
	 * 
	 * @param string $message
	 */
	public function __construct($message = null) {
		parent::__construct($message, 404);
	}
	
}
