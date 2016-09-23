<?php
namespace wiggum\exceptions;

use \Exception;

class PageNotFoundException extends Exception {
	
	/**
	 * 
	 */
	public function __construct($message = null) {
		parent::__construct($message, 404);
	}
	
}
?>