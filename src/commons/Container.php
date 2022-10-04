<?php
namespace wiggum\commons;

use \ArrayAccess;

class Container implements ArrayAccess
{
    
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
	public function __construct(array $values = [])
	{
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
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (isset($this->frozen[$offset])) {
			throw new \RuntimeException(sprintf('Cannot override frozen service "%s".', $offset));
		}

		$this->values[$offset] = $value;
		$this->keys[$offset] = true;
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet(mixed $offset): mixed
	{
		if (!isset($this->keys[$offset])) {
			throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $offset));
		}

		if (
			isset($this->raw[$offset])
			|| !is_object($this->values[$offset])
			|| isset($this->protected[$this->values[$offset]])
			|| !method_exists($this->values[$offset], '__invoke')
		) {
			return $this->values[$offset];
		}

		if (isset($this->factories[$this->values[$offset]])) {
			return $this->values[$offset]($this);
		}

		$raw = $this->values[$offset];
		$val = $this->values[$offset] = $raw($this);
		$this->raw[$offset] = $raw;

		$this->frozen[$offset] = true;

		return $val;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->keys[$offset]);
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset(mixed $offset): void
	{
		if (isset($this->keys[$offset])) {
			if (is_object($this->values[$offset])) {
				unset($this->factories[$this->values[$offset]], $this->protected[$this->values[$offset]]);
			}

			unset($this->values[$offset], $this->frozen[$offset], $this->raw[$offset], $this->keys[$offset]);
		}
	}

	/**
	 * 
	 * @param callable $callable
	 * @throws \InvalidArgumentException
	 * @return callable
	 */
	public function factory($callable)
	{
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
	public function protect($callable)
	{
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
	public function raw($id)
	{
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
	public function keys()
	{
		return array_keys($this->values);
	}
	
	/**
	 * 
	 * @param mixed $name
	 * @return mixed
	 */
	public function __get($name)
	{
	    return $this->offsetGet($name);
	}
	
	/**
	 * 
	 * @param mixed $name
	 * @return boolean
	 */
	public function __isset($name)
	{
	    return $this->offsetExists($name);
	}
	
}