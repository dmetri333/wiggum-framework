<?php
namespace wiggum\commons\util;

use \Exception;
use \LengthException;

class Encryption {
	
	private $key;
	
	/**
	 * 
	 * @param string $key [default=null]
	 * @throws LengthException
	 */
	public function __construct($key = null) {
		if (isset($key)) {	
			$key = str_replace(' ', '', $key);
			if ($key == '' || strlen($key) < 8)
				throw new LengthException("Key must be > 8 chars");
			
			$this->key = $key;
		}
	}
	
	/**
	 *
	 * @param string $plaintext
	 * @return string
	 * @throws Exception
	 * @throws LengthException
	 */
	public function encrypt($plaintext) {
		if (!isset($this->key)) throw new LengthException('No key set');
		if (!is_string($plaintext)) throw new Exception('Cannot encrypt non string type. [' . $plaintext . ']');
		$enc = $this->crypt($plaintext);
		return $enc;	
	}
	
	/**
	 * 
	 * @param string $enc
	 * @return string
	 * @throws Exception
	 * @throws LengthException
	 */
	public function decrypt($enc) {
		if (!isset($this->key)) throw new LengthException('No key set');
		if (!is_string($enc)) throw new Exception('Cannot decrypt non string type. [' . $enc . ']');
		$dec = $this->crypt($enc);
		return $dec;
	}
	
	/**
	 *
	 * @param string $plaintext
	 * @return string
	 */
	public function urlEncrypt($plaintext) {
		$enc = $this->encrypt($plaintext);
		$base64 = base64_encode($enc);
        $result = str_replace(array('+', '/', '='), array("-", "_", "."), $base64);
		return $result;
	}
	
	/**
	 *
	 * @param string $urlEncrypted
	 * @return string
	 */
	public function urlDecrypt($urlEncrypted) {
		$base64 = str_replace(array("-","_", "."), array("+","/", "="), $urlEncrypted);
		$enc = base64_decode($base64);
		$dec = trim($this->decrypt($enc));
		return $dec;
	}
	
	/**
	 * 
	 * @param array $tokens
	 * @param string $seperator [default='|']
	 * @return string
	 */
	public function generateToken(array $tokens, $seperator = '|') {
		$token = implode($seperator, $tokens);
		$tokenEnc = $this->urlEncrypt($token);
		return $tokenEnc;
	}
	
	/**
	 * 
	 * @param string $token
	 * @param string $seperator [default='|']
	 * @return array
	 */
	public function decryptToken($token, $seperator = '|') {
		$tokenDec = $this->urlDecrypt($token);
		$tokens = explode($seperator, $tokenDec);
		return $tokens;
	}
	
	/**
	 * 
	 * @param string $text
	 * @return string
	 */
	private function crypt($text) {

		// set key length to be no more than 32 characters
		$keyLength = strlen($this->key);
		if ($keyLength > 32) {
			$keyLength = 32;
		}
		
		$key = substr($this->key, 0, $keyLength);
		$textLength = strlen($text);
		
		$lomask = str_repeat("\x1f", $textLength);
		$himask = str_repeat("\xe0", $textLength);
		$k = str_pad("", $textLength, $key);
		
		$text = (($text ^ $k) & $lomask) | ($text & $himask);
		return $text;
	}
	
	/**
	 * 
	 * @param string $data
	 * @param string $salt
	 * @param string $algo [default='sha1']
	 * @return string
	 */
	public static function getHash($data, $salt, $algo = 'sha1') {
		return hash($algo, $salt . $data);
	}
	
}
?>