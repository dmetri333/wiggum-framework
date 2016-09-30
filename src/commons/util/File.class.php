<?php
namespace wiggum\commons\util;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

class File {
	
	/**
	 * Create a folder
	 * 
	 * @param string $fullPath - the directory to create
	 * @param boolean $makeParents [default=true] - true to create parent directories
	 * @return boolean
	 */
	public static function createFolder($fullPath, $makeParents = true) {
		if (file_exists($fullPath)) {
			return true;
		} else {
			$result = mkdir($fullPath, MKDIR_MODE, $makeParents);
			if(!$result) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Move a folder
	 * 
	 * @param string $source
	 * @param string $destination
	 */
	public static function moveFolder($source, $destination) {
		if (is_dir($source))
			rename($source, $destination);
	}
	
	/**
	 * Delete a directory
	 *
	 * @param string $dir - directory to delete
	 * @return boolean
	 */
	public static function deleteDirectory($dir) {
		if (is_dir($dir))
			$dirHandle = opendir($dir);
		
		if (!$dirHandle)
			return false;
		
		while ($file = readdir($dirHandle)) {
			if ($file != "." && $file != "..") {
				if (!is_dir($dir."/".$file))
					unlink($dir."/".$file);
				else
					self::deleteDirectory($dir.'/'.$file);          
			}
		}
		
		closedir($dirHandle);
		rmdir($dir);
		return true;
	}
	
	/**
	 * Copy a file
	 *
	 * @param string $source
	 * @param string $destination
	 * @return boolean
	 */
	public static function copyFile($source, $destination) {
		return copy($source, $destination);
	}
	
	/**
	 * Copy Contents
	 *
	 * @param string $src
	 * @param string $dst
	 * @return boolean
	 */
	public static function copyRecurse($src, $dst) { 
		$dir = opendir($src);
		@mkdir($dst); 
		while(false !== ($file = readdir($dir))) { 
			if (($file != '.') && ($file != '..')) { 
				if (is_dir($src . '/' . $file)) { 
					self::copyRecurse($src . '/' . $file, $dst . '/' . $file); 
				} else { 
					copy($src . '/' . $file, $dst . '/' . $file); 
				}
			} 
		}
		closedir($dir);
	} 
	
	/**
	 * Renames a file
	 *
	 * @param string $path
	 * @param string $oldname
	 * @param string $newname
	 * @return boolean
	 */
	public static function renameFile($path, $oldname, $newname) {
		return rename($path . $oldname, $path . $newname);
	}
	
	/**
	 * moves a file
	 *
	 * @param string $src
	 * @param string $dst
	 * @return boolean
	 */
	public static function moveFile($src, $dst) {
		return rename($src, $dst);
	}
	
	/**
	 * strips out the file extenstion
	 *
	 * @param string $fileName
	 * @return string
	 */
	public static function removeExtension($fileName) {
		$ext = strrchr($fileName, '.');
		if($ext !== false) {
			$fileName = substr($fileName, 0, -strlen($ext));
		}
		return $fileName;
	}
	
	/**
	 * Get file extension (part after last dot in file name)
	 * 
	 * @param string $fileName
	 * @return string
	 */
	public static function getExt($fileName) {
		$ext = strrchr($fileName, '.');
		return ($ext !== false) ? substr($ext,1) : '';
	}

	/**
	 * Get total size of files in directory and subdirectories
	 * 
	 * @param string $dir
	 * @return integer
	 */
	public static function getDirectorySize($dir) {
		if (is_dir($dir)) {
			$totalSize = 0;
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
			foreach($iterator as $file) {
				$totalSize += $file->getSize();
			}
			return $totalSize;
		}
		return false;
	}
}
?>