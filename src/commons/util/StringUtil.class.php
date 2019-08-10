<?php
namespace wiggum\commons\util;

/**
 * @deprecated
 * 
 *
 */
class StringUtil {
	
	/**
	 * 
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function beginsWith($haystack, $needle) {
		return (strncmp($haystack, $needle, strlen($needle)) == 0);
	}
	
	/**
	 * 
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function contains($haystack, $needle) {
		return (strpos($haystack, $needle) === false) ? false : true;
	}
	
	/**
	 * Remove HTML tags from a string.
	 * Can specify which tags to keep using optional allowTags array.
	 * 
	 * @param string $str
	 * @param array $allowTags [default=array()]
	 * @return string
	 */
	public static function removeTags($str, array $allowTags = array()) {
		$allowTags = implode(' ', $allowTags);
		//Note strip_tags may have problems with partial/broken tags
		return strip_tags($str, $allowTags);
	}
	
	/**
	 * check if string is a valid RFC 822 date format
	 * i.e. Fri, 22 Aug 2008 14:02:20 EDT
	 *
	 * @param string $str
	 * @return boolean
	 */
	public static function isValidRfc822($str) {
		static $regex = '/^[A-Z][a-z]{2}[,]\s[0-9]{2}\s[A-Z][a-z]{2}\s[0-9]{4}\s[0-9]{2}[:][0-9]{2}[:][0-9]{2}\s[A-Z]{3}$/';
		return preg_match($regex, $str);
	}
	
	/**
	 * Remove unwanted characters from a string.
	 * Characters in blacklist will be removed.
	 * Remaining characters not in blacklist or whitelist will be replaced with hyphen.
	 *
	 * @param string $str
	 * @return string
	 */
	public static function removeUnsafeChars($str) {
		static $blacklist = '/[!\"?():\']/';
		static $whitelist = '/[^a-zA-Z0-9-_.]/';
		$validStr = preg_replace(array($blacklist, $whitelist), array('', '-'), $str);
		return $validStr;
	}
	
	/**
	 * Adds a ellipses to the right of a string and trims down the size
	 * 
	 * @param string $string
	 * @param int $max
	 * @param string $ellipses [default='...']
	 * @return string
	 */
	public static function ellipsizeRight($string, $max, $ellipses = '...') {
		$string = trim($string);
		
		// don't ellipsize if the string is short enough
		if (strlen($string) <= $max) {
			return $string;
		}
		
		// chop at max length
		$string = substr($string, 0, $max);
		
		$chop = strrpos($string, ' ');
		if ($chop !== false)
			$string = substr($string, 0, $chop);
		
		$string = self::removeTrailingPunctuation($string);
		$string .= $ellipses;
		
		return $string;
	}
	
	/**
	 * Adds a ellipses to the middle of string and trims down the size
	 * 
	 * @param string $string
	 * @param int $max
	 * @param string $ellipses [default='...']
	 * @return string
	 */
	public static function ellipsizeMiddle($string, $max, $ellipses = '...') {
		$string = trim($string);
		$firstPiece = '';
		$lastPiece = '';

		// don't ellipsize if the string is short enough
		if (strlen($string) <= $max) {
			return $string;
		}
		
		// check if the string is all one giant word
		$hasSpace = strpos($string, ' ');
		
		// the entire string is one word
		if ($hasSpace === false) {
			$firstPiece = substr($string, 0, $max / 2);
			$lastPiece = substr($string, - ($max - strlen($firstPiece)));
		} else {
			
			$lastPiece = substr($string, - ($max / 2));
			$lastPiece = trim($lastPiece);
		
			$lastSpace = strrpos($lastPiece, ' ');
			if ($lastSpace !== false)
				$lastPiece = substr($lastPiece, $lastSpace + 1);
			
			$maxFirst = $max - strlen($lastPiece);
			
			$firstPiece = self::ellipsizeRight($string, $maxFirst, '');
		}
	
		$string = $firstPiece . $ellipses . $lastPiece;
		return $string;
	}
	
	/**
	 * Removed any punctuation at the and of the string
	 * 
	 * @param string $string
	 * @return string
	 */
	public static function removeTrailingPunctuation($string) {
		return preg_replace('/\W+$/su', '', $string);
	}

	/** 
	 * 
	 * @param array $request
	 * @param array $excludeList [default=array()]
	 * @return string
	 */
	public static function getQueryString(array $request, array $excludeList = array()) {
		$query = '';
		$first = true;
		foreach ($request as $key => $value) {
			if (!in_array($key, $excludeList)) {
				if(!$first) $query = "{$query}&";
	    		$query = "{$query}{$key}={$value}";
	    		$first = false;
			}
	    }
		return $query;
	}

	/**
	 * 
	 * @param array $string
	 * @param string $delimiter [default=',']
	 * @param string $keyValue [default='=>']
	 * @return array
	 */
	public static function convertStringToKeyedArray($string, $delimiter = ',', $keyValue = '=>') {
		if ($a = explode($delimiter, $string)) { // create parts
			foreach ($a as $s) { // each part
				if ($s) {
					if ($pos = strpos($s, $keyValue)) { // key/value delimiter
						$keyArray[trim(substr($s, 0, $pos))] = trim(substr($s, $pos + strlen($keyValue)));
					} else { // key delimiter not found
						$keyArray[] = trim($s);
					}
				}
			}
			return $keyArray;
		}
	}
	
	/**
	 * 
	 * @param string $data
	 * @return boolean
	 */
	public static function isSerialized($data) {
		// if it isn't a string, it isn't serialized
		//return (@unserialize($data) !== false);
		
		if (!is_string($data))
			return false;
		$data = trim($data);
		if ('N;' == $data)
			return true;
		if (!preg_match('/^([adObis]):/', $data, $badions))
			return false;
		switch ($badions[1]) {
			case 'a' :
			case 'O' :
			case 's' :
				if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
					return true;
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
					return true;
				break;
		}
		return false;
	}
	
}
?>