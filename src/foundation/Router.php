<?php
namespace wiggum\foundation;

use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\foundation\Application;

abstract class Router {
	
    protected $app;
    protected $registeredMiddleware;
    protected $registeredfilters;
    
	/**
	 * 
	 * @param array $app
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}

	/**
	 * 
	 * @param string $name
	 * @param \Closure $closure
	 */
	public function registerMiddleware($name, $closure) {
	    $this->registeredMiddleware[$name] = $closure;
	}
	
	/**
	 *
	 * @param string $name
	 * @param \Closure $closure
	 */
	public function registerFilter($name, $closure) {
	    $this->registeredfilters[$name] = $closure;
	}
	
	abstract public function map($methods, $pattern, $handler);
	abstract public function process(Request $request, Response $response);

}
?>