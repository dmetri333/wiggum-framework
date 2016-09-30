<?php
namespace wiggum\commons\template;

use LogicException;

class Template {
	
	protected $directory;
	protected $basePath;
	protected $fileExtension;
	protected $file;
	protected $path;
	
	private $vars = array();
	
	/**
	 *
	 * @param string $directory
	 * @param string $basePath
	 * @param string $fileExtension [default='tpl.php']
	 */
	public function __construct($directory, $basePath, $fileExtension = 'tpl.php') {
		$this->directory = $directory;
		$this->basePath = $basePath;
		$this->fileExtension = $fileExtension;
	}
	
	/**
	 * Set a template variable.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set($name, $value) {
		$this->vars[$name] = is_object($value) ? (method_exists($value, 'fetch') ? $value->fetch() : $value) : $value;
	}
	
	/**
	 * Appends all of the template variable to the end of the vars list
	 *
	 * @param array $values
	 */
	public function setAll(array $values) {
		foreach ($values as $name => $value) {
			$this->set($name, $value);
		}
	}
	
	/**
	 * Open, parse, and return the template file.
	 *
	 * @param string $file
	 */
	public function fetch($file) {
		$this->path = $this->path != '' ? $this->path : $this->basePath . '/' . $this->directory . '/' . $file . '.' . $this->fileExtension;
		
		extract($this->vars);			// Extract the vars to local namespace
		ob_start();						// Start output buffering
		include($this->path);					// Include the file
		$contents = ob_get_contents();	// Get the contents of the buffer
		ob_end_clean();					// End buffering and discard
		return $contents;				// Return the contents
	}
	
	/**
	 * 
	 * @param string $path
	 */
	public function setTemplatePath($path) {
		$this->path = $path;
	}
	
	/**
	 * Escape string.
	 * 
	 * @param string $string
	 * @param null|string $functions
	 * @return string
	 */
	protected function escape($string, $functions = null) {
		
		$flags = ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0);
		
		if ($functions) {
			$string = $this->batch($string, $functions);
		}
	
		return htmlspecialchars($string, $flags, 'UTF-8');
	}
	
	/**
	 * Alias to escape function.
	 * 
	 * @param string $string
	 * @param null|string $functions
	 * @return string
	 */
	protected function e($string, $functions = null) {
		return $this->escape($string, $functions);
	}
	
	/**
	 * Apply multiple functions to variable.
	 * 
	 * @param mixed $var
	 * @param string $functions
	 * @return mixed
	 */
	protected function batch($var, $functions) {
		foreach (explode('|', $functions) as $function) {
			if (is_callable($function)) {
				$var = call_user_func($function, $var);
			} else {
				throw new LogicException('The batch function could not find the "' . $function . '" function.');
			}
		}
		return $var;
	}
	
}
?>