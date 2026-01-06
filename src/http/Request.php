<?php
namespace wiggum\http;

#[\AllowDynamicProperties]
class Request
{
	
	private $requestURI;
	private $serverName;
	private $contextPath;
	private $contextPathSegments = [];
	private $method;
	private $parameters = [];
	private $attributes = [];
	private $cookies = [];
	private $files = [];
	private $body = '';
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
		return $this->contextPathSegments[$index] ?? null;
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
	
	public function getParameter(string $name, $default = null) : ?string
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : (isset($default) ? $default : null);
	}

	public function hasParameter(string $name) : bool
	{
		return isset($this->parameters[$name]);
	}
	
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}
	
	public function setParameter(string $name, $value)
	{
		if (!isset($this->parameters)) {
			$this->parameters = [];
		}
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
	
	public function getAttribute(string $name, $default = null)
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : (isset($default) ? $default : null);
	}
	
	public function setAttribute(string $name, $value)
	{
		if (!isset($this->attributes)) {
			$this->attributes = [];
		}
		$this->attributes[$name] = $value;
	}
	
	public function hasAttribute(string $name) : bool
	{
		return isset($this->attributes[$name]);
	}
	
	public function getCookies()
	{
		return $this->cookies;
	}
	
	public function getCookie(string $name, $default = null) : ?string
	{
		return isset($this->cookies[$name]) ? $this->cookies[$name] : (isset($default) ? $default : null);
	}
	
	public function hasCookie(string $name) : bool
	{
		return isset($this->cookies[$name]);
	}
	
	public function setCookies(array $cookies)
	{
		$this->cookies = $cookies;
	}
	
	public function getBody() : string 
	{
		return $this->body;
	}

	public function withBody(string $body) : void
	{
		$this->body = $body;
	}
	
	public function getFiles()
	{
		return $this->files;
	}

	public function getFile(string $name)
	{
		return $this->files[$name] ?? null;
	}
	
	public function setFiles(array $files)
	{
		$this->files = [];
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
	
	private function reorderMultipleFiles(array $files) : array
	{
		$result = [];
		foreach($files as $key1 => $value1) {
			foreach($value1 as $key2 => $value2) {
				$result[$key2][$key1] = $value2;
			}
		}
		return $result;
	}
	
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
	}
	
	public function getHeaders() : array
	{
		return $this->headers;
	}
	
	public function hasHeader(string $name) : bool
	{
		return isset($this->headers[$name]);
	}
	
	public function addHeader(string $name, $value)
	{
		if (!isset($this->headers)) {
			$this->headers = [];
		}
		$this->headers[$name] = $value;
	}
	
	public function getHeader(string $name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : null;
	}
	
}
