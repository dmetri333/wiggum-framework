<?php
namespace wiggum\foundation;

abstract class Controller
{
	
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
	public function getApplication(): Application
	{
		return $this->app;
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
	public function has(string $name): bool
	{
		return $this->app->getContainer()->offsetExists($name);
	}
	
}