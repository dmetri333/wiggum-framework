<?php
namespace wiggum\http\interfaces;

interface Router {
	
	/**
	 * 
	 * @param array $methods
	 * @param string $pattern
	 * @param mixed $handler
	 * @return Route
	 */
	public function map(array $methods, string $pattern, $handler): Route;
	
	/**
	 * 
	 * @param Request $request
	 * @return Route
	 */
	public function lookup(Request $request): Route;
	
}
