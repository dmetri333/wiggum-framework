<?php
namespace wiggum\exceptions;

use \Exception;
use \wiggum\commons\helpers\StatusCodeHelper;

class InternalErrorException extends Exception
{
	
	/**
	 * 
	 * @param string $message
	 */
	public function __construct(string $message = null)
	{
		$message = isset($message) ? $message : StatusCodeHelper::getReasonPhrase(500);
		parent::__construct($message, 500);
		header('HTTP/1.1 500 Internal Server Error');
	}
	
}
