<?php
namespace wiggum\commons\helpers;

class TextHelper
{

    /**
     *
     * @param string $string
     * @param int max
     * @param float (1|0) or float, .5, .2, etc for position to split
     * @param string ellipsis
     * @return string
     */
    public static function ellipsize($string, $max, $position = 1, $ellipsis = '&hellip;')
    {
        // Strip tags
        $string = trim(strip_tags($string));
        
        // Is the string long enough to ellipsize?
        if (mb_strlen($string) <= $max) {
            return $string;
        }
        
        $begin = mb_substr($string, 0, floor($max * $position));
        $position = ($position > 1) ? 1 : $position;
        
        if ($position === 1) {
            $end = mb_substr($string, 0, -($max - mb_strlen($begin)));
        } else {
            $end = mb_substr($string, -($max - mb_strlen($begin)));
        }
        
        return $begin.$ellipsis.$end;
    }
    
    /**
     * High ASCII to Entities
     *
     * Converts high ASCII text and MS Word special characters to character entities
     *
     * @param	string	$str
     * @return	string
     */
    public static function asciiToEntities($str)
    {
        $out = '';
        $length = defined('MB_OVERLOAD_STRING') ? mb_strlen($str, '8bit') - 1 : strlen($str) - 1;
        
        for ($i = 0, $count = 1, $temp = []; $i <= $length; $i++) {
            $ordinal = ord($str[$i]);
            if ($ordinal < 128) {
                /*
                 If the $temp array has a value but we have moved on, then it seems only
                 fair that we output that entity and restart $temp before continuing. -Paul
                 */
                if (count($temp) === 1) {
                    $out .= '&#'.array_shift($temp).';';
                    $count = 1;
                }
                $out .= $str[$i];
            } else {
                if (count($temp) === 0) {
                    $count = ($ordinal < 224) ? 2 : 3;
                }
                $temp[] = $ordinal;
                if (count($temp) === $count) {
                    $number = ($count === 3)
                    ? (($temp[0] % 16) * 4096) + (($temp[1] % 64) * 64) + ($temp[2] % 64)
                    : (($temp[0] % 32) * 64) + ($temp[1] % 64);
                    $out .= '&#'.$number.';';
                    $count = 1;
                    $temp = [];
                }
                // If this is the last iteration, just output whatever we have
                else if ($i === $length) {
                    $out .= '&#'.implode(';', $temp).';';
                }
            }
        }
        
        return $out;
    }

    /**
     * Entities to ASCII
     *
     * Converts character entities back to ASCII
     *
     * @param	string
     * @param	bool
     * @return	string
     */
    public static function entitiesToAscii($str, $all = true)
    {
        if (preg_match_all('/\&#(\d+)\;/', $str, $matches)) {
            for ($i = 0, $s = count($matches[0]); $i < $s; $i++) {
                $digits = $matches[1][$i];
                $out = '';
                if ($digits < 128) {
                    $out .= chr($digits);
                } else if ($digits < 2048) {
                    $out .= chr(192 + (($digits - ($digits % 64)) / 64)).chr(128 + ($digits % 64));
                } else {
                    $out .= chr(224 + (($digits - ($digits % 4096)) / 4096))
                    .chr(128 + ((($digits % 4096) - ($digits % 64)) / 64))
                    .chr(128 + ($digits % 64));
                }
                $str = str_replace($matches[0][$i], $out, $str);
            }
        }
        
        if ($all) {
            return str_replace(['&amp;', '&lt;', '&gt;', '&quot;', '&apos;', '&#45;'], ['&', '<', '>', '"', "'", '-'], $str);
        }
        
        return $str;
    }
   
    
    
}