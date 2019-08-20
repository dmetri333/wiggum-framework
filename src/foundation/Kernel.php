<?php
namespace wiggum\foundation;

abstract class Kernel {

	private $app;

	/**
	 * Start the engine
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
		
		$this->app->loadConfig($this->loadConfigurationFiles($this->app->basePath.DIRECTORY_SEPARATOR.'config'));
		$this->app->loadEnvironment();
		$this->app->loadBootFiles();
	}
	
	/**
	 * 
	 * @return mixed
	 */
	abstract public function run();
	
	/**
	 * 
	 * @param string $path
	 * @return array
	 */
	private function loadConfigurationFiles(string $path) : array
	{
	    $files = scandir($path);
	    
	    $items = [];
	    foreach ($files as $file) {
	        if ($file != '.' && $file != '..') {
	            $items[pathinfo($file, PATHINFO_FILENAME)] = require $path .DIRECTORY_SEPARATOR. $file;
	        }
	    }
	    return $items;
	}
	
}