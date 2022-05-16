<?php
namespace wiggum\exceptions;

use \Exception;
use \wiggum\commons\helpers\StatusCodeHelper;

class PageNotFoundException extends Exception
{
	
	/**
	 * 
	 * @param string $message
	 */
	public function __construct(string $message = null)
	{
		$message = isset($message) ? $message : StatusCodeHelper::getReasonPhrase(404);
		parent::__construct($message, 404);
	}
	
}
