<?php
namespace wiggum\http;

class Runner {

	protected $queue = [];

	/**
	 * 
	 * @param array $queue
	 */
	public function __construct(array $queue)
	{
		$this->queue = $queue;
	}

	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	public function __invoke(Request $request, Response $response) : Response
	{
		$entry = array_shift($this->queue);
		$middleware = $this->resolve($entry);
		
		return $middleware($request, $response, $this);
	}

	/**
	 *
	 * @param callable $entry
	 * @return Response
	 */
	protected function resolve(callable $entry)
	{
		
		if (!$entry) {
			// the default callable when the queue is empty
			return function (Request $request, Response $response, callable $next) {
				return $response;
			};
		}
		
		return $entry;
	}
	
}