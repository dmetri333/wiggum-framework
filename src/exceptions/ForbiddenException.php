<?php
namespace wiggum\exceptions;

use \Exception;
use \wiggum\commons\helpers\StatusCodeHelper;

class ForbiddenException extends Exception
{
	
	/**
	 * 
	 * @param string $message
	 */
	public function __construct(string $message = null)
	{
		$message = isset($message) ? $message : StatusCodeHelper::getReasonPhrase(403);
		parent::__construct($message, 403);
		header('HTTP/1.1 403 Forbidden');
	}
	
}