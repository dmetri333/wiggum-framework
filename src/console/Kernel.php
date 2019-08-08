<?php
namespace wiggum\console;

use \Exception;
use \Throwable;
use \wiggum\foundation\Application;

class Kernel {

	private $app;

	/**
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app) {
		$this->app = $app;
		
		$this->app->loadConfig($this->loadConfigurationFiles($this->app->basePath.DIRECTORY_SEPARATOR.'config'));
		$this->app->loadEnvironment();
		$this->app->loadBootFiles();
	}
	
	/**
	 * 
	 * @return string
	 */
	public function run() {
	   
	    $response = $this->process();
	
		$this->respond($response);
		return $response;
	}
	
	/**
	 * 
	 * @throws \RuntimeException
	 * @return string
	 */
	private function process() {
	    global $argv;
	    
	    try {
	        if (count($argv) <= 1) {
	            throw new \RuntimeException('Command not found');
	        }
	        
            $command = $argv[1];
            $args = array_slice($argv, 2);
            
            $possibleCommands = $this->app->config->get('commands');
            if (!array_key_exists($command, $possibleCommands)) {
                throw new \RuntimeException('Command not found');
            }
            
            $class = $possibleCommands[$command];
            
            // Bail if class doesn't exist
            if (!class_exists($class)) {
                throw new \RuntimeException(sprintf('Class %s does not exist', $class));
            }
            
            $task = new $class($this->app);
            
            if (!method_exists($task, 'command')) {
                throw new \RuntimeException(sprintf('Class %s does not have a command() method', $class));
            }
            
            return $task->command($args);
            
	    } catch (Exception $e) {
	        return $e->getMessage();
	    } catch (Throwable $e) {
	        return $e->getMessage();
	    }
	    
	}
	
	/**
	 * 
	 * @param string $response
	 */
	private function respond($response) {
		echo $response;
		echo "\n";
	}
	
	/**
	 *
	 * @param string $path
	 
	 * @return array
	 */
	private function loadConfigurationFiles($path) {
	    $files = scandir($path);
	    
	    $items = [];
	    foreach ($files as $file) {
	        if ($file != '.' && $file != '..')
	            $items[pathinfo($file, PATHINFO_FILENAME)] = require $path .DIRECTORY_SEPARATOR. $file;
	    }
	    return $items;
	}
	
}