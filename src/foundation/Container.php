<?php
namespace wiggum\foundation;

use \wiggum\foundation\Application;

class Container implements \ArrayAccess {
	private $values = array();
	private $factories;
	private $protected;
	private $frozen = array();
	private $raw = array();
	private $keys = array();
	private $app;
	
	public function __construct(Application $app, array $values = array()) {
		$this->app = $app;
		
		$this->factories = new \SplObjectStorage();
		$this->protected = new \SplObjectStorage();

		foreach ($values as $key => $value) {
			$this->offsetSet($key, $value);
		}
	}

	public function offsetSet($id, $value) {
		if (isset($this->frozen[$id])) {
			throw new \RuntimeException(sprintf('Cannot override frozen service "%s".', $id));
		}

		$this->values[$id] = $value;
		$this->keys[$id] = true;
	}

	public function offsetGet($id) {
		if (!isset($this->keys[$id])) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		if (
			isset($this->raw[$id])
			|| !is_object($this->values[$id])
			|| isset($this->protected[$this->values[$id]])
			|| !method_exists($this->values[$id], '__invoke')
		) {
			return $this->values[$id];
		}

		if (isset($this->factories[$this->values[$id]])) {
			return $this->values[$id]($this->app);
		}

		$raw = $this->values[$id];
		$val = $this->values[$id] = $raw($this->app);
		$this->raw[$id] = $raw;

		$this->frozen[$id] = true;

		return $val;
	}
	
	public function offsetExists($id) {
		return isset($this->keys[$id]);
	}

	public function offsetUnset($id) {
		if (isset($this->keys[$id])) {
			if (is_object($this->values[$id])) {
				unset($this->factories[$this->values[$id]], $this->protected[$this->values[$id]]);
			}

			unset($this->values[$id], $this->frozen[$id], $this->raw[$id], $this->keys[$id]);
		}
	}

	public function factory($callable) {
		if (!is_object($callable) || !method_exists($callable, '__invoke')) {
			throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
		}

		$this->factories->attach($callable);

		return $callable;
	}

	public function protect($callable) {
		if (!is_object($callable) || !method_exists($callable, '__invoke')) {
			throw new \InvalidArgumentException('Callable is not a Closure or invokable object.');
		}

		$this->protected->attach($callable);

		return $callable;
	}

	public function raw($id) {
		if (!isset($this->keys[$id])) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		if (isset($this->raw[$id])) {
			return $this->raw[$id];
		}

		return $this->values[$id];
	}

	public function keys() {
		return array_keys($this->values);
	}
	
	public function __get($name) {
		return $this->offsetGet($name);
	}
	
	public function __isset($name) {
		return $this->offsetExists($name);
	}
	
}