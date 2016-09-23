<?php
namespace wiggum\foundation;

use \Closure;

class Configuration {
	
	/**
	 * All of the configuration items.
	 *
	 * @var array
	 */
	protected $items = [];
	
	/**
	 *
	 * @param array $items        	
	 * @return void
	 */
	public function __construct(array $items = []) {
		$this->items = $items;
	}
	
	/**
	 *
	 * @param string $key        	
	 * @return bool
	 */
	public function has($key) {
		if (!$this->items) {
			return false;
		}
		
		if (is_null($key)) {
			return false;
		}
		
		if (array_key_exists($key, $this->items)) {
			return true;
		}
		
		$items = $this->items;
		foreach (explode('.', $key) as $segment) {
			if (is_array($items) && array_key_exists($segment, $items)) {
				$items = $items[$segment];
			} else {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 *
	 * @param string $key        	
	 * @param mixed $default        	
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if (is_null($key)) {
			return $this->items;
		}
		
		if (array_key_exists($key, $this->items)) {
			return $this->items[$key];
		}
		
		$items = $this->items;
		foreach (explode('.', $key) as $segment) {
			if (is_array($items) && array_key_exists($segment, $items)) {
				$items = $items[$segment];
			} else {
				return $default instanceof Closure ? $default() : $default;
			}
		}
		
		return $items;
	}
	
	/**
	 *
	 * @param array|string $key        	
	 * @param mixed $value        	
	 * @return void
	 */
	public function set($key, $value = null) {
		
		if (is_array($key)) {
			foreach ($key as $innerKey => $innerValue) {
				$this->doSet($this->items, $innerKey, $innerValue);
			}
		} else {
			$this->doSet($this->items, $key, $value);
		}
		
	}
	
	/**
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	private function doSet(&$array, $key, $value) {
		if (is_null($key)) {
			return $array = $value;
		}
	
		$keys = explode('.', $key);
		while (count($keys) > 1) {
			$key = array_shift($keys);
	
			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if (!isset($array[$key]) || !is_array($array[$key])) {
				$array[$key] = [];
			}
	
			$array = &$array[$key];
		}
	
		$array[array_shift($keys)] = $value;
	
		return $array;
	}
	
	/**
	 *
	 * @return array
	 */
	public function all() {
		return $this->items;
	}
	
}