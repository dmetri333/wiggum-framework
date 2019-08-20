<?php
namespace wiggum\exceptions;

use \Exception;

class ForbiddenException extends Exception {
	
	/**
	 * 
	 * @param string $message
	 */
	public function __construct(string $message = null)
	{
		parent::__construct($message, 403);
		header('HTTP/1.1 403 Forbidden');
	}
	
}