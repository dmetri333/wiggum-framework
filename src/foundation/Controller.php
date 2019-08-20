<?php
namespace wiggum\foundation;

use wiggum\http\Request;
use wiggum\http\Response;

abstract class Controller {
	
	protected $app;
	
	/**
	 * 
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	
	/**
	 * 
	 * @return Application
	 */
	public function getApplication() : Application
	{
		return $this->app;
	}
	
	/**
	 * @deprecated use service $this->dictionary->get($name, $language)
	 * 
	 * @param string $name
	 * @param string $language
	 * @return array
	 */
	public function getDictionary(string $name, string $language = null)
	{
	    return $this->dictionary->get($name, $language);
	}
	
	/**
	 * @deprecated use service $this->dictionary->replace($name, $replace, $language)
	 * 
	 * @param string $name
	 * @param array $replace
	 * @param string $language
	 */
	public function getDictionaryReplace(string $name, array $replace, string $language = null)
	{
	    return $this->dictionary->replace($name, $replace, $language);
	}
	
	/**
	 * 
	 * @param string $classPath
	 * @param string $method
	 * @param Request $request
	 * @return Response
	 */
	public function forward(string $classPath, string $method = 'doDefault', Request $request = null) : Response
	{
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
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if ($this->app->getContainer()->offsetExists($name)) {
			$obj = $this->app->getContainer()->offsetGet($name);
			return $obj;
		}
		
		throw new \Exception('Unrecognized property ' . $name);
	}
	
	/**
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function has(string $name) : bool
	{
		return $this->app->getContainer()->offsetExists($name);
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	public abstract function doDefault(Request $request, Response $response);
	
}