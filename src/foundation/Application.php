<?php
namespace wiggum\foundation;

use \wiggum\commons\Container;
use \wiggum\commons\Configuration;

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
	public function __construct($basePath) {
		$this->setBasePath($basePath);
		
		$this->container = new Container();
		$this->middleware = [];
		$this->routes = [];
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
		$this->basePath  = rtrim($basePath, '\/');
	}
	
	/**
	 * 
	 * @param array $settings
	 */
	public function loadSettings(array $settings) {
		$this->settings = new Configuration($settings);
	}
	
	/**
	 * 
	 */
	public function loadBootFiles() {
		$app = $this;
	
		$bootFiles = $this->settings->get('config.boot');
		foreach ($bootFiles as $bootFile) {
			require_once $this->basePath.DIRECTORY_SEPARATOR.$bootFile;
		}
	}
	
	/**
	 *
	 */
	public function loadEnvironment() {
		date_default_timezone_set($this->settings->get('config.timezone'));
		
		mb_internal_encoding('UTF-8');
	}
	
}