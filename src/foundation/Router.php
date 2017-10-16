<?php
namespace wiggum\foundation;

use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\foundation\Application;
use \wiggum\exceptions\PageNotFoundException;
use \InvalidArgumentException;

abstract class Router {
	
    protected $app;
    protected $routes;
    
	/**
	 * 
	 * @param array $app
	 */
	public function __construct(Application $app) {
		$this->app = $app;
	}
	
	/**
	 * 
	 * @param array $actions
	 * @param Request $request
	 * @param Response $response
	 * @throws PageNotFoundException
	 * @return Response
	 */
	public function execute($actions, Request $request, Response $response) {
	    
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
        return $controller->$method($request, $response);
	}
	
	/**
	 *
	 * @param string|array $methods
	 * @param string $pattern
	 * @param mixed $handler
	 * @throws InvalidArgumentException
	 */
	public function map($methods, $pattern, $handler) {
	    
        if (!is_string($pattern)) {
            throw new InvalidArgumentException('Route pattern must be a string');
	    }
	    
        $methods  = is_string($methods) ? [$methods] : $methods;
	    
        // According to RFC methods are defined in uppercase (See RFC 7231)
        $methods = array_map("strtoupper", $methods);
	    
        $route = ['methods' => $methods, 'pattern' => $pattern, 'route' => $handler];
	    
        $this->routes[$pattern] = $route;
        
        return $route;
	}
	
	abstract public function process(Request $request, Response $response);

}
?>