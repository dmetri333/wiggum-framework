<?php
namespace wiggum\commons;

use \wiggum\http\Request;

abstract class Handler
{
	
	protected $knownContentTypes = [ 
			'application/json',
			'application/xml',
			'text/xml',
			'text/html' 
	];
	
	/**
	 *
	 * @param Request $request        	
	 * @return string
	 */
	protected function determineContentType(Request $request): string
	{
		$acceptHeader = $request->getHeader('Accept');
		$selectedContentTypes = array_intersect(explode(',', $acceptHeader), $this->knownContentTypes);
		
		if (count($selectedContentTypes)) {
			return current($selectedContentTypes);
		}
		
		// handle +json and +xml specially
		if (preg_match ('/\+(json|xml)/', $acceptHeader, $matches)) {
			$mediaType = 'application/' . $matches[1];
			if (in_array($mediaType, $this->knownContentTypes)) {
				return $mediaType;
			}
		}
		
		return 'text/html';
	}

	/**
	 * Write to the error log if displayErrorDetails is false
	 *
	 * @param \Exception|\Throwable $throwable
	 *
	 * @return void
	 */
	protected function writeToErrorLog($throwable): void
	{
		$message = 'Wiggum Application Error:' . PHP_EOL;
	
		$message .= $this->renderErrorAsText($throwable);
		while ($throwable = $throwable->getPrevious()) {
			$message .= PHP_EOL . 'Previous error:' . PHP_EOL;
			$message .= $this->renderErrorAsText($throwable);
		}
	
		$this->logError($message);
	}
	
	/**
	 * Render error as Text.
	 *
	 * @param \Exception|\Throwable $error
	 *
	 * @return string
	 */
	protected function renderErrorAsText($error): string
	{
		$text = sprintf('Type: %s' . PHP_EOL, get_class($error));
	
		if ($code = $error->getCode()) {
			$text .= sprintf('Code: %s' . PHP_EOL, $code);
		}
	
		if ($message = $error->getMessage()) {
			$text .= sprintf('Message: %s' . PHP_EOL, htmlentities($message));
		}
	
		if ($file = $error->getFile()) {
			$text .= sprintf('File: %s' . PHP_EOL, $file);
		}
	
		if ($line = $error->getLine()) {
			$text .= sprintf('Line: %s' . PHP_EOL, $line);
		}
	
		if ($trace = $error->getTraceAsString()) {
			$text .= sprintf('Trace: %s', $trace);
		}
	
		return $text;
	}
	
	/**
	 * Wraps the error_log function so that this can be easily tested
	 *
	 * @param string $message
	 */
	protected function logError(string $message): void
	{
		error_log($message);
	}

}