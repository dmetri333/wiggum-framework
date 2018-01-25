<?php
namespace wiggum\foundation;

use \wiggum\http\Request;
use \wiggum\foundation\Application;

abstract class Router {
	
    protected $app;
    
	/**
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}
	
	/**
	 * 
	 * @param array $methods
	 * @param string $pattern
	 * @param mixed $handler
	 */
	abstract public function map($methods, $pattern, $handler);
	
	/**
	 * 
	 * @param Request $request
	 */
	abstract public function dispatch(Request $request);
	
	/**
	 * 
	 */
	abstract public function process();
	
}
?>