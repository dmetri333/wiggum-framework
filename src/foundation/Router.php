<?php
namespace wiggum\foundation;

use \wiggum\http\Request;

abstract class Router {
	
    protected $app;
    
	/**
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	
	/**
	 * 
	 * @param array $methods
	 * @param string $pattern
	 * @param mixed $handler
	 */
	abstract public function map(array $methods, string $pattern, $handler) : Route;
	
	/**
	 * 
	 * @param Request $request
	 */
	abstract public function lookup(Request $request) : Route;
	
}