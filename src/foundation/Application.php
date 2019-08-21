<?php
namespace wiggum\foundation;

use \wiggum\commons\Container;
use \wiggum\commons\Configuration;

class Application {

	public $basePath;
	public $config;
	
	private $container;
	private $middleware;
	private $commands;
	
	/**
	 * Create new application
	 * 
	 * @param string $basePath
	 */
	public function __construct(string $basePath)
	{
		$this->setBasePath($basePath);
		
		$this->container = new Container();
		$this->middleware = [];
		$this->commands = [];
	}
	
	/**
	 * 
	 * @param string $name
	 * @param callable $service
	 */
	public function addService(string $name, callable $service): void
	{
		$this->container[$name] = $service;
	}
	
	/**
	 *
	 * @param callable $middleware
	 */
	public function addMiddleware(callable $middleware): void
	{
		$this->middleware[] = $middleware;
	}
	
	/**
	 *
	 * @param string $commands
	 */
	public function addCommand(string $command, string $classPath): void
	{
	    $this->commands[$command] = $classPath;
	}
	
	/**
	 * Enable access to the DI container
	 *
	 * @return Container
	 */
	public function getContainer(): Container
	{
		return $this->container;
	}
	
	/**
	 *
	 */
	public function getMiddleware(): array
	{
		return $this->middleware;
	}
	
	/**
	 *
	 */
	public function getCommands(): array
	{
	    return $this->commands;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBasePath(): string
	{
		return $this->basePath;
	}
	
	/**
	 * Set the base path for the application.
	 *
	 * @param string $basePath
	 */
	public function setBasePath(string $basePath): void
	{
		$this->basePath  = rtrim($basePath, '\/');
	}
	
	/**
	 * 
	 * @param array $config
	 */
	public function loadConfig(array $config): void
	{
		$this->config = new Configuration($config);
	}
	
	/**
	 * Calling a non-existant var on Controller checks to see if there's an item
	 * in the container that is callable and if so, calls it.
	 *
	 * @param string $method
	 * @return mixed
	 */
	public function __get(string $name)
	{
	    if ($this->container->offsetExists($name)) {
	        $obj = $this->container->offsetGet($name);
	        return $obj;
	    }
	    
	    throw new \Exception('Unrecognized property ' . $name);
	}
	
}