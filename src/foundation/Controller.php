<?php
namespace wiggum\foundation;

use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\foundation\Application;

abstract class Controller {
	
	protected $app;
	
	/**
	 * 
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param string $language
	 * @return array
	 */
	public function getDictionary($name, $language = null) {
		$dictionary = $this->app->config->get('dictionary', []);
		
		$name = isset($language) ? $language.'.'.$name : $name;
		if (!isset($dictionary[$name])) {
			return null;
		}
		
		return $dictionary[$name];
	}
	
	/**
	 * 
	 * @param string $name
	 * @param array $replace
	 * @param string $language
	 */
	public function getDictionaryReplace($name, array $replace, $language = null) {
		return str_replace(array_keys($replace), array_values($replace), $this->getDictionary($name, $language));
	}
	
	/**
	 * 
	 * @param string $classPath
	 * @param string $method
	 * @param Request $request
	 */
	public function forward($classPath, $method = 'doDefault', Request $request = null) {
		$component = new $classPath($this->app);
		
		$properties = get_object_vars($this);
		foreach ($properties as $propertyName => $propertyValue) {
			$component->$propertyName = $propertyValue;
		}
		
		if (!isset($request)) {
			$request = new Request();
		}
		
		return $component->$method($request, new Response());
	}
	
	/**
	 * Calling a non-existant var on Controller checks to see if there's an item
	 * in the container that is callable and if so, calls it.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __get($name) {
		if ($this->app->getContainer()->offsetExists($name)) {
			$obj = $this->app->getContainer()->offsetGet($name);
			return $obj;
		}
	
		throw new \Exception('Unrecognized property '.$name);
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	public abstract function doDefault(Request $request, Response $response);
	
}
?>