<?php
namespace wiggum\http;

class Request
{
	
	private $requestURI;
	private $serverName;
	private $contextPath;
	private $contextPathSegments;
	private $method;
	private $parameters;
	private $attributes;
	private $cookies;
	private $files;
	private $headers = [];
	
	public function getRequestURI()
	{
		return $this->requestURI;
	}
	
	public function setRequestURI($requestURI)
	{
		$this->requestURI = $requestURI;
	}
	
	public function getServerName()
	{
		return $this->serverName;
	}
	
	public function setServerName($serverName)
	{
		$this->serverName = $serverName;
	}
	
	public function getContextPath()
	{
		return $this->contextPath;
	}
	
	public function setContextPath($contextPath)
	{
		$this->contextPath = $contextPath;
	}
	
	public function getContextPathSegment($index)
	{
		return $this->contextPathSegments[$index];
	}
	
	public function getContextPathSegments()
	{
		return $this->contextPathSegments;
	}
	
	public function setContextPathSegments(array $contextPathSegments)
	{
		$contextPathSegments = array_slice($contextPathSegments, 1);
		$this->contextPathSegments = $contextPathSegments;
	}
	
	public function getMethod()
	{
		return $this->method;
	}
	
	public function setMethod($method)
	{
		$this->method = $method;
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
	
	public function getParameter($name, $default = null)
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : (isset($default) ? $default : null);
	}

	public function hasParameter($name)
	{
		return isset($this->parameters[$name]);
	}
	
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}
	
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}
	
	public function getAttributes()
	{
		return $this->attributes;
	}
	
	public function setAttributes(array $attributes)
	{
		$this->attributes = $attributes;
	}
	
	public function getAttribute($name, $default = null)
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : (isset($default) ? $default : null);
	}
	
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
	}
	
	public function hasAttribute($name)
	{
		return isset($this->attributes[$name]);
	}
	
	public function getCookies()
	{
		return $this->cookies;
	}
	
	public function getCookie($name, $default = null)
	{
		return isset($this->cookies[$name]) ? $this->cookies[$name] : (isset($default) ? $default : null);
	}
	
	public function hasCookie($name)
	{
		return isset($this->cookies[$name]);
	}
	
	public function setCookies(array $cookies)
	{
		$this->cookies = $cookies;
	}
	
	public function getFiles()
	{
		return $this->files;
	}

	public function getFile($name)
	{
		return $this->files[$name];
	}
	
	public function setFiles(array $files)
	{
		$this->files = array();
		if (!empty($files)) {
			foreach($files as $key => $value) {
				if (is_array($value['name'])) {
					$this->files[$key] = $this->reorderMultipleFiles($value);
				} else {
					$this->files[$key] = $value;
				}
			}
		}
	}
	
	private function reorderMultipleFiles(array $files)
	{
		$result = array();
		foreach($files as $key1 => $value1) {
			foreach($value1 as $key2 => $value2) {
				$result[$key2][$key1] = $value2;
			}
		}
		return $result;
	}
	
	public function setHeaders($headers)
	{
		$this->headers = $headers;
	}
	
	public function getHeaders()
	{
		return $this->headers;
	}
	
	public function hasHeader($name)
	{
		return isset($this->headers[$name]);
	}
	
	public function addHeader($name, $value)
	{
		$this->headers[$name] = $value;
	}
	
	public function getHeader($name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : null;
	}
	
}
