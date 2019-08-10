<?php
namespace wiggum\exceptions;

use \Exception;

class InternalErrorException extends Exception {
	
	/**
	 * 
	 */
	public function __construct($message = null) {
		parent::__construct($message, 500);
		header('HTTP/1.1 500 Internal Server Error');
	}
	
}
