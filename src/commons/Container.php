<?php
namespace wiggum\commons;

use \ArrayAccess;

class Container implements ArrayAccess {
    
	private $values = [];
	private $factories;
	private $protected;
	private $frozen = [];
	private $raw = [];
	private $keys = [];
	
	/**
	 * 
	 * @param array $values
	 */
	public function __construct(array $values = []) {
		$this->factories = new \SplObjectStorage();
		$this->protected = new \SplObjectStorage();

		foreach ($values as $key => $value) {
			$this->offsetSet($key, $value);
		}
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($id, $value) {
		if (isset($this->frozen[$id])) {
			throw new \RuntimeException(sprintf('Cannot override frozen service "%s".', $id));
		}

		$this->values[$id] = $value;
		$this->keys[$id] = true;
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetGet()
	 */
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
			return $this->values[$id]($this);
		}

		$raw = $this->values[$id];
		$val = $this->values[$id] = $raw($this);
		$this->raw[$id] = $raw;

		$this->frozen[$id] = true;

		return $val;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($id) {
		return isset($this->keys[$id]);
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($id) {
		if (isset($this->keys[$id])) {
			if (is_object($this->values[$id])) {
				unset($this->factories[$this->values[$id]], $this->protected[$this->values[$id]]);
			}

			unset($this->values[$id], $this->frozen[$id], $this->raw[$id], $this->keys[$id]);
		}
	}

	/**
	 * 
	 * @param callable $callable
	 * @throws \InvalidArgumentException
	 * @return callable
	 */
	public function factory($callable) {
		if (!is_object($callable) || !method_exists($callable, '__invoke')) {
			throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
		}

		$this->factories->attach($callable);

		return $callable;
	}

	/**
	 * 
	 * @param callable $callable
	 * @throws \InvalidArgumentException
	 * @return callable
	 */
	public function protect($callable) {
		if (!is_object($callable) || !method_exists($callable, '__invoke')) {
			throw new \InvalidArgumentException('Callable is not a Closure or invokable object.');
		}

		$this->protected->attach($callable);

		return $callable;
	}

	/**
	 * 
	 * @param string $id
	 * @throws \InvalidArgumentException
	 * @return mixed
	 */
	public function raw($id) {
		if (!isset($this->keys[$id])) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		}

		if (isset($this->raw[$id])) {
			return $this->raw[$id];
		}

		return $this->values[$id];
	}

	/**
	 * 
	 * @return array
	 */
	public function keys() {
		return array_keys($this->values);
	}
	
	/**
	 * 
	 * @param mixed $name
	 * @return mixed
	 */
	public function __get($name) {
	    return $this->offsetGet($name);
	}
	
	/**
	 * 
	 * @param mixed $name
	 * @return boolean
	 */
	public function __isset($name) {
	    return $this->offsetExists($name);
	}
	
}