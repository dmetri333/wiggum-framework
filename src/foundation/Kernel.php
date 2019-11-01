<?php
namespace wiggum\foundation;

use \wiggum\commons\helpers\FileHelper;

abstract class Kernel
{
	
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
	protected function loadConfigurationFiles(string $path): array
	{
	    $files = scandir($path);
	    
	    $items = [];
	    foreach ($files as $file) {
	        if ($file != '.' && $file != '..' && $file[0] !== '.' && FileHelper::extension($file) == 'php') {
	            $items[pathinfo($file, PATHINFO_FILENAME)] = require $path .DIRECTORY_SEPARATOR. $file;
	        }
	    }
	    return $items;
	}
	
	/**
	 * 
	 * @param Application $app
	 * @param array $bootFiles
	 */
	protected function loadBootFiles(Application $app, array $bootFiles): void
	{
	    foreach ($bootFiles as $bootFile) {
	        require_once $app->basePath.DIRECTORY_SEPARATOR.$bootFile;
	    }
	}
	
	/**
	 * 
	 * @param Application $app
	 */
	protected function loadEnvironment(Application $app): void
	{
	    date_default_timezone_set($app->config->get('app.timezone', 'UTC'));
	    mb_internal_encoding($app->config->get('app.encoding', 'UTF-8'));
	}
	
}