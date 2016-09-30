<?php
namespace wiggum\foundation;

use \wiggum\commons\Container;

class Application {

	public $basePath;
	public $settings;
	
	private $container;
	private $middleware;
	private $routes;
	

	/**
	 * Create new application
	 *
	 */
	public function __construct($basePath, $settings) {
		$this->settings = $settings;
		$this->basePath = $basePath;
		
		$this->container = new Container();
		$this->middleware = [];
		$this->routes = [];
		
		$this->load();
	}
	
	/**
	 * 
	 * @param string $name
	 * @param closer $service
	 */
	public function addService($name, $service) {
		$this->container[$name] = $service;
	}
	
	/**
	 * 
	 * @param closer $middlewaree
	 */
	public function addMiddleware($middleware) {
		$this->middleware[] = $middleware;
	}
	
	/**
	 * 
	 * @param string $pattern
	 * @param mixed $route
	 */
	public function addRoute($pattern, $route) {
		$this->routes[$pattern] = $route;
	}
	
	/**
	 * Enable access to the DI container
	 *
	 * @return Container
	 */
	public function getContainer() {
		return $this->container;
	}
	
	/**
	 *
	 */
	public function getMiddleware() {
		return $this->middleware;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getRoutes() {
		return $this->routes;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}
	
	/**
	 * Set the base path for the application.
	 *
	 * @param  string  $basePath
	 */
	public function setBasePath($basePath) {
		$this->basePath = $basePath;
	}
	
	/**
	 *
	 */
	public function load() {
		$app = $this;
	
		$config = $this->settings->get('config');
		
		//add services
		require_once $this->basePath.DIRECTORY_SEPARATOR.$config['boot.services'];
	
		//add middleware
		require_once $this->basePath.DIRECTORY_SEPARATOR.$config['boot.middleware'];
	
		//add routes
		require_once $this->basePath.DIRECTORY_SEPARATOR.$config['boot.routes'];
	
	}
	
}