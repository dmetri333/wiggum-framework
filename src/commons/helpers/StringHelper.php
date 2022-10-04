<?php
namespace wiggum\commons\helpers;

class StringHelper
{
    
    /**
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        if (empty($needle)) return false;
        
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
    
    /**
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        if (empty($needle)) return false;
        
        return (substr($haystack, -strlen($needle)) === (string) $needle);
    }
    
    /**
     * Replace the first occurrence of a given value in the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {
        if ($search == '') {
            return $subject;
        }
        
        $position = strpos($subject, $search);
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        
        return $subject;
    }
    
    /**
     * Replace the last occurrence of a given value in the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        $position = strrpos($subject, $search);
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        return $subject;
    }
    
    /**
     * @param string $str
     * @param string $separator
     * @param string $first
     * @return string
     */
    public static function incrementString(string $str, string $separator = '-', int $first = 1): string
    {
        preg_match('/(.+)'.preg_quote($separator, '/').'([0-9]+)$/', $str, $match);
        return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
    }
    
    /**
     *
     * @param string $type [basic, alpha, alnum, numeric, nozero, md5, and sha1]
     * @param int $len
     * @return string
     */
    public static function randomString(string $type = 'alnum', int $len = 8): string
    {
        switch ($type) {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type) {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'sha1':
                return sha1(uniqid(mt_rand(), true));
        }
    }
    
    /**
     * 
     * @param string $str
     * @param string $character
     * @param bool $trim - trim the character from the beginning/end
     * @return string
     */
    public static function reduceMultiples(string $str, string $character = ',', bool $trim = false): string
    {
        $str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);
        return ($trim === true) ? trim($str, $character) : $str;
    }

}