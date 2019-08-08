<?php
namespace wiggum\foundation;

use \Exception;
use \Throwable;
use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\exceptions\PageNotFoundException;

class Kernel {

	private $app;

	/**
	 * start the engine
	 *
	 */
	public function __construct(Application $app) {
		$this->app = $app;
		
		$this->app->loadConfig($this->loadConfigurationFiles($this->app->basePath.DIRECTORY_SEPARATOR.'config'));
		$this->app->loadEnvironment();
		$this->app->loadBootFiles();
	}
	
	/**
	 * 
	 * @return Response
	 */
	public function run() {
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
	 * 
	 * @return Response
	 */
	private function process(Request $request, Response $response) {
        try {
	        
	        $router = $this->app->getContainer()->offsetGet('router');
	        
	        $route = $router->lookup($request);
	        $route = $router->preProcess($route);
	        
	        $this->app->addMiddleware(function(Request $request, Response $response, callable $next) use ($route, $router) {
	            
	            $actions = $router->process($route);
	            $response = $this->executeRoute($actions, $request, $response);
	            
	            $response = $next($request, $response);
	            
	            return $response;
	        });
	            
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
	 *
	 * @return Response
	 */
	private function callMiddlewareStack(Request $request, Response $response) {
		$runner = new Runner($this->app->getMiddleware());
		$response = $runner($request, $response);
	
		return $response;
	}
	
	/**
	 * 
	 * @return Request
	 */
	private function buildRequest() {
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
	private function respond(Response $response) {
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
	 * @param array $actions
	 * @param Request $request
	 * @param Response $response
	 * @throws PageNotFoundException
	 * @return Response
	 */
	private function executeRoute(array $actions, Request $request, Response $response) {
	    
	    if (empty($actions))
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
	 * @param  Exception $e
	 * @param  Request $request
	 * @param  Response $response
	 * 
	 * @return Response
	 * @throws Exception
	 */
	private function handleException(Exception $e, Request $request, Response $response) {
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
	 * 
	 * @return Response
	 * @throws Throwable
	 */
	private function handlePhpError(Throwable $e, Request $request, Response $response) {
		if ($this->app->getContainer()->offsetExists('errorHandler')) {
			$callable = $this->app->getContainer()->offsetGet('errorHandler');
			// Call the registered handler
			return call_user_func_array($callable, [$request, $response, $e]);
		}
	
		// No handlers found, so just throw the exception
		throw $e;
	}
	
	
	/**
	 * 
	 * @param string $path

	 * @return array
	 */
	private function loadConfigurationFiles($path) {
	    $files = scandir($path); //['app','services','dictionary'];
	    
	    $items = [];
	    foreach ($files as $file) {
	        if ($file != '.' && $file != '..') {
	            $items[pathinfo($file, PATHINFO_FILENAME)] = require $path .DIRECTORY_SEPARATOR. $file;
	        }
	    }
	    return $items;
	}
	
}