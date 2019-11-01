<?php
namespace wiggum\console;

use \Exception;
use \Throwable;
use \wiggum\foundation\Application;

class Kernel extends \wiggum\foundation\Kernel
{

    protected $app;
    
    /**
     * Start the engine
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        
        $this->app->loadConfig($this->loadConfigurationFiles($this->app->basePath.DIRECTORY_SEPARATOR.'config'));
        
        $this->loadEnvironment($this->app);
        $this->loadBootFiles($this->app, $this->app->config->get('app.boot.cli', []));
    }
    
	/**
	 * 
	 * @return string
	 */
	public function run()
	{
	    if (!$this->isCli()) {
	        throw new \RuntimeException('Command must run in cli!');
	    }
	    
	    $response = $this->process();
	
		$this->respond($response);
		return $response;
	}
	
	/**
	 * 
	 * @throws \RuntimeException
	 * @return string
	 */
	private function process(): string
	{
	    global $argv;
	    
	    try {
	        if (count($argv) <= 1) {
	            throw new \RuntimeException('Command not found');
	        }
	        
            $command = $argv[1];
            $args = array_slice($argv, 2);
            
            $possibleCommands = $this->app->getCommands();
            if (!array_key_exists($command, $possibleCommands)) {
                throw new \RuntimeException('Command not found');
            }
            
            $class = $possibleCommands[$command];
            
            // Bail if class doesn't exist
            if (!class_exists($class)) {
                throw new \RuntimeException(sprintf('Class %s does not exist', $class));
            }
            
            $task = new $class($this->app);
            
            if (!method_exists($task, 'handle')) {
                throw new \RuntimeException(sprintf('Class %s does not have a handle() method', $class));
            }
            
            return $task->handle($args);
            
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
	private function respond(string $response): void
	{
		echo $response;
		echo "\n";
	}
	
	/**
	 * 
	 * @return bool
	 */
	private function isCli(): bool
	{
	    if (defined('STDIN')) {
	        return true;
	    }
	    
	    if (php_sapi_name() === 'cli') {
	        return true;
	    }
	    
	    if (array_key_exists('SHELL', $_ENV)) {
	        return true;
	    }
	    
	    if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
	        return true;
	    }
	    
	    if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
	        return true;
	    }
	    
	    return false;
	}
	
}