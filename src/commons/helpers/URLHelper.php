<?php
namespace wiggum\commons\helpers;

class URLHelper
{

    /**
     * @param string $str
     * @param bool $popup
     * @return string
     */
    public static function autoLink(string $str, bool $popup = false): string
    {
        
        // Find and replace any URLs.
        if (preg_match_all('#(\w*://|www\.)[a-z0-9]+(-+[a-z0-9]+)*(\.[a-z0-9]+(-+[a-z0-9]+)*)+(/([^\s()<>;]+\w)?/?)?#i', $str, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            // Set our target HTML if using popup links.
            $target = ($popup) ? ' target="_blank" rel="noopener"' : '';
            // We process the links in reverse order (last -> first) so that
            // the returned string offsets from preg_match_all() are not
            // moved as we add more HTML.
            foreach (array_reverse($matches) as $match) {
                // $match[0] is the matched string/link
                // $match[1] is either a protocol prefix or 'www.'
                //
                // With PREG_OFFSET_CAPTURE, both of the above is an array,
                // where the actual value is held in [0] and its offset at the [1] index.
                $a = '<a href="'.(strpos($match[1][0], '/') ? '' : 'http://').$match[0][0].'"'.$target.'>'.$match[0][0].'</a>';
                $str = substr_replace($str, $a, $match[0][1], strlen($match[0][0]));
            }
        }
        
        // Find and replace any emails.
        if (preg_match_all('#([\w\.\-\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+[^[:punct:]\s])#i', $str, $matches, PREG_OFFSET_CAPTURE)) {
            foreach (array_reverse($matches[0]) as $match) {
                if (filter_var($match[0], FILTER_VALIDATE_EMAIL) !== false) {
                    $str = substr_replace($str, self::mailto($match[0]), $match[1], strlen($match[0]));
                }
            }
        }
        
        return $str;
    }


    /**
     * 
     * @param string $str
     * @param string $separator
     * @param bool $lowercase
     * @param bool $utf8
     * @return string
     */
    public static function slug(string $str, string $separator = '-', bool $lowercase = true, bool $utf8 = true): string
    {
 
        $q_separator = preg_quote($separator, '#');
        $trans = [
            '&.+?;'			        => '',
            '[^\w\d _-]'		    => '',
            '\s+'			        => $separator,
            '('.$q_separator.')+'	=> $separator
        ];
        
        $str = strip_tags($str);
        foreach ($trans as $key => $val) {
            $str = preg_replace('#'.$key.'#i'.($utf8 ? 'u' : ''), $val, $str);
        }
        
        if ($lowercase) {
            $str = strtolower($str);
        }
        
        return trim(trim($str, $separator));
    }
    
    /**
     * 
     * @param string $email
     * @param string $title
     * @param string $attributes
     * @return string
     */
    private static function mailto(string $email, string $title = '', string $attributes = ''): string
    {
        $title = (string) $title;
        if ($title === '') {
            $title = $email;
        }
        
        return '<a href="mailto:'.$email.'"'.self::stringifyAttributes($attributes).'>'.$title.'</a>';
    }
    
    /**
     * 
     * @param string $attributes
     * @param boolean $js
     * @return string
     */
    private static function stringifyAttributes($attributes, bool $js = false): ?string
    {
		$atts = '';
        if (empty($attributes)) {
			return null;
        }
        
        if (is_string($attributes)) {
            return ' '.$attributes;
        }
        
        $attributes = (array) $attributes;
        foreach ($attributes as $key => $val) {
            $atts .= ($js) ? $key.'='.$val.',' : ' '.$key.'="'.$val.'"';
        }
        
        return rtrim($atts, ',');
    }

}