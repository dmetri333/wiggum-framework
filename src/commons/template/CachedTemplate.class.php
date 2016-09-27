<?php
namespace wiggum\commons\template;

class CachedTemplate extends Template {
	
	private $cacheId;
	private $expire;
	private $cached;
	private $cachePath;
	private $ignore;

	/**
	 *
	 * @param string $cacheId - unique cache identifier
	 * @param int $expire - [default=900] number of seconds the cache will live
	 */
	public function __construct($cacheId, $cachePath, $directory, $expire = 900, $basePath = BASE_PATH, $fileExtension = 'tpl.php') {
		parent::__construct($directory, $basePath, $fileExtension);
		
		$this->cacheId = $cacheId ? md5($cacheId) : $cacheId;
		$this->expire = $expire;
		$this->cachePath = $cachePath;
		$this->ignore = false;
	}

	/**
	 * Test to see whether the currently loaded cacheId has a valid
	 * corrosponding cache file.
	 * 
	 * @return boolean
	 */
	public function isCached() {
		if ($this->cached) return true;

		// Passed a cacheId?
		if (!$this->cacheId) return false;

		// Cache file exists?
		if (!file_exists($this->cachePath . $this->cacheId)) return false;

		// Can get the time of the file?
		if (!($mtime = filemtime($this->cachePath . $this->cacheId))) return false;

		// Cache expired?
		if (($mtime + $this->expire) < time()) {
			unlink($this->cachePath . $this->cacheId);
			return false;
		} else {
			/**
			 * Cache the results of this isCached() call.  Why?  So
			 * we don't have to double the overhead for each template.
			 * If we didn't cache, it would be hitting the file system
			 * twice as much (file_exists() & filemtime() [twice each]).
			 */
			$this->cached = true;
			return true;
		}
	}

	/**
	 * 
	 */
	public function deleteCache() {
		if (file_exists($this->cachePath . $this->cacheId))
			unlink($this->cachePath . $this->cacheId);
	}
	
	/**
	 * 
	 */
	public function ignoreCache() {
		$this->ignore = true;
	}
	
	/**
	 * This function returns a cached copy of a template (if it exists),
	 * otherwise, it parses it as normal and caches the content.
	 *
	 * @param string $file - the template file
	 * @return string
	 */
	public function fetchCache($file) {
		if ($this->isCached()) {
			$fp = fopen($this->cachePath .$this->cacheId, 'r');
			$contents = fread($fp, filesize($this->cachePath . $this->cacheId));
			fclose($fp);
			return $contents;
		} else {
			$path = $this->basePath . $this->directory . '/' . $file . '.' . $this->fileExtension;
			
			$contents = $this->fetch($path);
			if (!$this->ignore) {
				// Write the cache
				if ($fp = fopen($this->cachePath . $this->cacheId, 'w')) {
					fwrite($fp, $contents);
					fclose($fp);
				} else {
					die('Unable to write cache.');
				}
			}
			
			return $contents;
		}
	}
	
}
?>