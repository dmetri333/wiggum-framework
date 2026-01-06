<?php
namespace wiggum\http;

use \wiggum\http\Request;
use \wiggum\http\Response;

abstract class Controller extends \wiggum\foundation\Controller
{
	
	/**
	 * 
	 * @param string $classPath
	 * @param string $method
	 * @param Request $request
	 * @return Response
	 */
	public function forward(string $classPath, string $method = 'doDefault', ?Request $request = null): Response
	{
		$component = new $classPath($this->app);
		
		$properties = get_object_vars($this);
		foreach ($properties as $propertyName => $propertyValue) {
			$component->$propertyName = $propertyValue;
		}
		
		if (!isset($request)) {
			$request = new Request();
		}
		
		return $component->$method($request, new Response());
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	public abstract function doDefault(Request $request, Response $response);
	
}