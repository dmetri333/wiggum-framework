<?php
namespace wiggum\http;

class Response
{
	
	private $contentType;
	private $headers = [];
	private $output = '';
	private $redirect;
	private $code;
	private $reasonPhrase;
	
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
	}
	
	public function getContentType()
	{
		return $this->contentType;
	}
	
	public function setHeaders($headers)
	{
		$this->headers = $headers;
	}
	
	public function getHeaders()
	{
		return $this->headers;
	}
	
	public function addHeader($name, $value)
	{
		$this->headers[$name] = $value;
	}
	
	public function getHeader($name)
	{
		return $this->headers[$name] ?? null;
	}
	
	public function setOutput($output)
	{
		$this->output = $output;
	}
	
	public function appendOutput($output)
	{
		$this->output .= (string) $output;
	}
	
	public function getOutput()
	{
		return $this->output;
	}
	
	public function setRedirect($redirect)
	{
		$this->redirect = $redirect;
	}
	
	public function getRedirect()
	{
		return $this->redirect;
	}
	
	public function withStatus($code, $reasonPhrase = null)
	{
		$this->code = $code;
		$this->reasonPhrase = $reasonPhrase;
	}
	
	public function getReasonPhrase()
	{
		return $this->reasonPhrase;
	}
	
	public function getStatusCode()
	{
		return $this->code;
	}

}