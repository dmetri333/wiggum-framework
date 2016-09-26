<?php
namespace wiggum\foundation;

use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\foundation\Application;
use \wiggum\exceptions\PageNotFoundException;

class Router {
	
	private $app;
	
	/**
	 * 
	 * @param array $app
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @throws PageNotFoundException
	 */
	public function process(Request $request, Response $response) {
		
		$actions = $this->parseURL($request);
	
		if (!isset($actions))
			throw new PageNotFoundException();

		if (!isset($actions['classPath']))
			throw new PageNotFoundException();
		
		$controller = new $actions['classPath']($this->app);

		if (isset($actions['parameters'])) {
			$request->setParameters(array_merge($request->getParameters(), $actions['parameters']));
		}

		if (isset($actions['properties'])) {
			foreach ($actions['properties'] as $property => $value) {
				$controller->{$property} = $value;
			}
		}

		$method = isset($actions['method']) && method_exists($controller, $actions['method']) ? $actions['method'] : 'doDefault';
		return $controller->$method($request, new Response());

	}
	
	/**
	 * 
	 * @param Request $request
	 * @return multitype:array |NULL
	 */
	private function parseURL(Request $request) {
		$routing = $this->app->getRoutes();
		
		$path = $request->getContextPath();
		if ($path == '/' && isset($routing[$path])) {
			return $this->controllerActions([], $routing[$path]);
		}
	
		$segments = $request->getContextPathSegments();
		
		if (isset($routing[$segments[0]])) {
			return $this->controllerActions($segments, $routing[$segments[0]]);
		}

		// Loop through the routes array looking for wild-cards
		foreach ($routing as $route => $data) {
			$regex = is_array($data) && isset($data['regex']) && $data['regex'] == true;
			if ($regex || is_callable($data)) {
				// Convert wild-cards to RegEx
				$route = str_replace('*', '.*', $route);
				
				// Does the RegEx match?
				if (preg_match('#^'.$route.'$#', $path, $matches)) {
					array_shift($matches);
					return $this->controllerActions($segments, $data, $matches);
				}
			}
		}
		
		//check final wildcard
		if (isset($routing['*'])) {
			return $this->controllerActions([], $routing['*']);
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param array $actions
	 * @param mixed $data
	 * @param array $parameters
	 * @return multitype:array | null
	 */
	private function controllerActions(array $segments, $data, array $parameters = []) {
		
		$actions = [];
		if (is_string($data)) {
			$actions['classPath'] = $data;
		
			if (isset($segments[1]) && $segments[1] != '') {
				$actions['method'] = $segments[1];
			}
			
			if (!empty($parameters)) {
				$actions['parameters'] = $parameters;
			}
		
			return $actions;
		} else if (is_array($data)) {
			if (isset($data['classPath'])) {
				$actions['classPath'] = $data['classPath'];
			}
			
			if (isset($data['method'])) {
				$actions['method'] = $data['method'];
			} else if (isset($segments[1]) && $segments[1] != '') {
				$actions['method'] = $segments[1];
			}
			
			if (!empty($parameters)) {
				$actions['parameters'] = $parameters;
			}
			
			return $actions;
		} else if (is_callable($data)) {
			return (array) call_user_func_array($data, [$segments, $parameters]);
		}
		
		return null;
	}

}
?>