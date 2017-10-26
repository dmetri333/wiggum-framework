<?php
namespace wiggum\foundation;

use \wiggum\commons\Container;
use \wiggum\commons\Configuration;

class Application {

	public $basePath;
	public $config;
	
	private $container;
	private $middleware;
	

	/**
	 * Create new application
	 *
	 */
	public function __construct($basePath) {
		$this->setBasePath($basePath);
		
		$this->container = new Container();
		$this->middleware = [];
	}
	
	/**
	 * 
	 * @param string $name
	 * @param mixed $service
	 */
	public function addService($name, $service) {
		$this->container[$name] = $service;
	}
	
	/**
	 *
	 * @param mixed $middlewaree
	 */
	public function addMiddleware($middleware) {
		$this->middleware[] = $middleware;
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
	 * @param array $config
	 */
	public function loadConfig(array $config) {
		$this->config = new Configuration($config);
	}
	
	/**
	 * 
	 */
	public function loadBootFiles() {
		$app = $this;
	
		$bootFiles = $this->config->get('app.boot');
		foreach ($bootFiles as $bootFile) {
			require_once $this->basePath.DIRECTORY_SEPARATOR.$bootFile;
		}
	}
	
	/**
	 *
	 */
	public function loadEnvironment() {
		date_default_timezone_set($this->config->get('app.timezone', 'UTC'));
		
		mb_internal_encoding('UTF-8');
	}
	
	/**
	 * Calling a non-existant var on Controller checks to see if there's an item
	 * in the container that is callable and if so, calls it.
	 *
	 * @param string $method
	 * @return mixed
	 */
	public function __get($name) {
	    if ($this->container->offsetExists($name)) {
	        $obj = $this->container->offsetGet($name);
	        return $obj;
	    }
	    
	    throw new \Exception('Unrecognized property ' . $name);
	}
	
}