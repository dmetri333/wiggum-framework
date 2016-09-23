<?php
namespace wiggum\middleware;

use \wiggum\http\Request;
use \wiggum\http\Response;

class Runner {

	protected $queue = [];

	/**
	 * 
	 * @param array $queue
	 * @param callable $resolver
	 */
	public function __construct(array $queue) {
		$this->queue = $queue;
	}

	/**
	 * 
	 * @param \wiggum\http\Request $request
	 * @param \wiggum\http\Response $response
	 */
	public function __invoke(Request $request, Response $response) {
		$entry = array_shift($this->queue);
		$middleware = $this->resolve($entry);
		
		return $middleware($request, $response, $this);
	}

	/**
	 * 
	 * @param unknown $entry
	 * @return \wiggum\http\Response
	 */
	protected function resolve($entry) {
		
		if (!$entry) {
			// the default callable when the queue is empty
			return function (Request $request, Response $response, callable $next) {
				return $response;
			};
		}
		
		return $entry;
	}
	
}