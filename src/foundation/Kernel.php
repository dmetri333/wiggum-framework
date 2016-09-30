<?php
namespace wiggum\foundation;

use \Exception;
use \Throwable;
use \wiggum\commons\Configuration;
use \wiggum\http\Request;
use \wiggum\http\Response;
use \wiggum\foundation\Runner;
use \wiggum\foundation\Router;
use \wiggum\foundation\Application;

class Kernel {

	private $app;

	/**
	 * Create new application
	 *
	 */
	public function __construct($basePath) {
		$basePath = rtrim($basePath, '\/');
		$configPath = $basePath.DIRECTORY_SEPARATOR.'config';
		
		$settings = new Configuration($this->loadConfigurationFiles($configPath));

		date_default_timezone_set($settings->get('config.timezone'));
		
		mb_internal_encoding('UTF-8');
		
		$this->app = new \wiggum\foundation\Application($basePath, $settings);
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
			
			$this->app->addMiddleware(function(Request $request, Response $response, callable $next) {
			
				$router = new Router($this->app);
				$response = $router->process($request, $response);
					
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
	 * Move to kernel
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @return unknown
	 */
	private function callMiddlewareStack(Request $request, Response $response) {
		$runner = new Runner($this->app->getMiddleware());
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
		if ($this->app->getContainer()->offsetExists('exceptionHandler')) {
			$callable = $this->app->getContainer()->offsetGet('exceptionHandler');
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
		if ($this->app->getContainer()->offsetExists('errorHandler')) {
			$callable = $this->app->getContainer()->offsetGet('errorHandler');
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