<?php
namespace wiggum\foundation;

use \Exception;
use \Throwable;
use \wiggum\commons\Container;
use \wiggum\commons\Configuration;
use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\foundation\Runner;
use \wiggum\foundation\Router;


class Application {

	public $settings;
	
	private $basePath;
	private $container;
	private $middleware = [];
	private $routes = [];

	/**
	 * Create new application
	 *
	 */
	public function __construct($basePath) {
		
		$this->setBasePath($basePath);
		
		$this->settings = new Configuration($this->loadConfigurationFiles($this->getConfigPath()));
	
		$this->container = new Container($this);
	}
	
	/**
	 * 
	 * @param string $name
	 * @param closer $service
	 */
	public function addService($name, $service) {
		$this->container[$name] = $service;
	}
	
	/**
	 * 
	 * @param closer $middlewaree
	 */
	public function addMiddleware($middlewaree) {
		$this->middleware[] = $middlewaree;
	}
	
	/**
	 * 
	 * @param string $pattern
	 * @param mixed $route
	 */
	public function addRoute($pattern, $route) {
		$this->routes[$pattern] = $route;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getRoutes() {
		return $this->routes;
	}
	
	/**
	 * Enable access to the DI container
	 *
	 * @return Container
	 */
	public function getContainer() {
		return $this->container;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}
	
	/**
	 * Set the base path for the application.
	 *
	 * @param  string  $basePath
	 */
	public function setBasePath($basePath) {
		$this->basePath = rtrim($basePath, '\/');
	}
	
	/**
	 *
	 * @return string
	 */
	public function getConfigPath() {
		return $this->basePath.DIRECTORY_SEPARATOR.'config';
	}
	
	/**
	 *
	 * @return string
	 */
	public function getBootPath() {
		return $this->basePath.DIRECTORY_SEPARATOR.'boot';
	}
	
	/**
	 * Move to kernel
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
	 * Move to kernel
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 */
	private function process(Request $request, Response $response) {
	
		try {
			$response = $this->callMiddlewareStack($request, $response);
		} catch (Exception $e) {
			$response = $this->handleException($e, $request, $response);
		} catch (Throwable $e) {
			$response = $this->handlePhpError($e, $request, $response);
		}
	
		return $response;
	}
	
	/**
	 * Move to kernel
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @return unknown
	 */
	private function callMiddlewareStack(Request $request, Response $response) {
	
		$this->middleware[] = function(Request $request, Response $response, callable $next) {
				
			$router = new Router($this);
			$response = $router->process($request, $response);
			
			$response = $next($request, $response);
				
			return $response;
		};
	
		$runner = new Runner($this->middleware);
		$response = $runner($request, $response);
	
		return $response;
	}
	
	/**
	 * Move to kernel
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
	 * Move to kernel
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
	 * Move to kernel
	 * 
	 * @param  Exception $e
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 * @throws Exception
	 */
	private function handleException(Exception $e, Request $request, Response $response) {
		if ($this->container->offsetExists('exceptionHandler')) {
			$callable = $this->container->offsetGet('exceptionHandler');
			// Call the registered handler
			return call_user_func_array($callable, [$request, $response, $e]);
		}
	
		// No handlers found, so just throw the exception
		throw $e;
	}
	
	/**
	 * Move to kernel
	 * 
	 * @param  Throwable $e
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 * @throws Throwable
	 */
	private function handlePhpError(Throwable $e, Request $request, Response $response) {
		if ($this->container->offsetExists('errorHandler')) {
			$callable = $this->container->offsetGet('errorHandler');
			// Call the registered handler
			return call_user_func_array($callable, [$request, $response, $e]);
		}
	
		// No handlers found, so just throw the exception
		throw $e;
	}
	
	
	/**
	 * Move to kernel
	 * 
	 * @param string $path
	 * @param array $files
	 */
	private function loadConfigurationFiles($path) {
		$files = ['config','services','dictionary'];
		
		$items = [];
		foreach ($files as $file) {
			$items[$file] = require $path .DIRECTORY_SEPARATOR. $file .'.php';
		}
		return $items;
	}
	
}