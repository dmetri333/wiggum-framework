<?php
namespace wiggum\http;

use \Exception;
use \Throwable;
use \wiggum\exceptions\PageNotFoundException;

class Kernel extends \wiggum\foundation\Kernel {
	
	/**
	 * 
	 * @return Response
	 */
	public function run()
	{
		$request = $this->buildRequest();
		$response = new Response();
	
		$response = $this->process($request, $response);
	
		$this->respond($response);
	
		return $response;
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	private function process(Request $request, Response $response) : Response
	{
        try {
	        
	        $route = $this->lookupRoute($request);
	        $actions = $route->process();
	        
            $this->app->addMiddleware(function(Request $request, Response $response, callable $next) use ($actions) {
	            
                $response = $this->executeRoute($actions, $request, $response);
	            
	            $response = $next($request, $response);
	            
	            return $response;
	        });
	        
            $request->setParameters(array_merge($request->getParameters(), $route->getParameters()));
                
	        $response = $this->callMiddlewareStack($request, $response);
	    } catch (Exception $e) {
	        $response = $this->handleException($e, $request, $response);
	    } catch (Throwable $e) {
	        $response = $this->handlePhpError($e, $request, $response);
	    }
	    
	    return $response;
	}
	
	/**
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	private function callMiddlewareStack(Request $request, Response $response) : Response
	{
		$runner = new Runner($this->app->getMiddleware());
		$response = $runner($request, $response);
	
		return $response;
	}
	
	/**
	 * 
	 * @return Request
	 */
	private function buildRequest() : Request
	{
		$request = new Request();
		$request->setRequestURI($_SERVER['REQUEST_URI']);
		$request->setServerName($_SERVER['SERVER_NAME']);
		$request->setContextPath(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		$request->setContextPathSegments(explode('/', $request->getContextPath()));
		$request->setMethod($_SERVER['REQUEST_METHOD']);
		$request->setParameters(array_merge($_GET, $_POST));
		$request->setCookies($_COOKIE);
		$request->setFiles($_FILES);
		$request->setHeaders(getallheaders());
		
		return $request;
	}
	
	/**
	 * 
	 * @param Response $response
	 */
	private function respond(Response $response) : void
	{
		$redirect = $response->getRedirect();
		if (isset($redirect)) {
			header('location: ' . $response->getRedirect());
		} else {
			$headers = $response->getHeaders();
			foreach ($headers as $name => $value) {
				header($name .': '. $value);
			}
	
			http_response_code($response->getStatusCode());
	
			$contentType = $response->getContentType();
			if (isset($contentType)) {
				header('Content-Type: '. $response->getContentType());
			}
	
			echo $response->getOutput();
		}
	}
	
	/**
	 *
	 * @param Request $request
	 * @return Route
	 */
	private function lookupRoute(Request $request) : Route
	{
	    // Get loaded router
	    $router = $this->app->router;
	    
	    // do lookup
	    $route = $router->lookup($request);
	    
	    // add route middleware
	    foreach ($route->getMiddleware() as $middleware) {
	        $this->app->addMiddleware($middleware);
	    }
	    
	    return $route;
	}
	
	/**
	 *
	 * @param array $actions
	 * @param Request $request
	 * @param Response $response
	 * @throws PageNotFoundException
	 * @return Response
	 */
	private function executeRoute(array $actions, Request $request, Response $response) : Response
	{
	    if (empty($actions))
	        throw new PageNotFoundException();
	        
        if (!isset($actions['classPath']))
            throw new PageNotFoundException();
            
        $controller = new $actions['classPath']($this->app);
    
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
	 * @param  Exception $e
	 * @param  Request $request
	 * @param  Response $response
	 * @throws Exception
	 * @return mixed
	 */
	private function handleException(Exception $e, Request $request, Response $response)
	{
		if ($this->app->getContainer()->offsetExists('exceptionHandler')) {
			$callable = $this->app->getContainer()->offsetGet('exceptionHandler');
			// Call the registered handler
			return call_user_func_array($callable, [$request, $response, $e]);
		}
	
		// No handlers found, so just throw the exception
		throw $e;
	}
	
	/**
	 * 
	 * @param  Throwable $e
	 * @param  Request $request
	 * @param  Response $response
	 * @throws Throwable
	 * @return mixed
	 */
	private function handlePhpError(Throwable $e, Request $request, Response $response)
	{
		if ($this->app->getContainer()->offsetExists('errorHandler')) {
			$callable = $this->app->getContainer()->offsetGet('errorHandler');
			// Call the registered handler
			return call_user_func_array($callable, [$request, $response, $e]);
		}
	
		// No handlers found, so just throw the exception
		throw $e;
	}
	
}