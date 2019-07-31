<?php
namespace wiggum\commons\helpers;

class SecurityHelper
{
    
    protected static $xssHash;
    
    protected static $charset = 'UTF-8';
    
    protected static $naughtyTags    = [
        'alert', 'area', 'prompt', 'confirm', 'applet', 'audio', 'basefont', 'base', 'behavior', 'bgsound',
        'blink', 'body', 'embed', 'expression', 'form', 'frameset', 'frame', 'head', 'html', 'ilayer',
        'iframe', 'input', 'button', 'select', 'isindex', 'layer', 'link', 'meta', 'keygen', 'object',
        'plaintext', 'style', 'script', 'textarea', 'title', 'math', 'video', 'svg', 'xml', 'xss'
    ];
    
    protected static $evilAttributes = [
        'on\w+', 'style', 'xmlns', 'formaction', 'form', 'xlink:href', 'FSCommand', 'seekSegmentTime'
    ];
    
    protected static $attributesPattern = '#'
        .'(?<name>[^\s\042\047>/=]+)' // attribute characters
        // optional attribute-value
    .'(?:\s*=(?<value>[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))' // attribute-value separator
    .'#i';
    
    protected static $neverAllowedStr =	[
        'document.cookie'   => '[removed]',
        '(document).cookie' => '[removed]',
        'document.write'    => '[removed]',
        '(document).write'  => '[removed]',
        '.parentNode'       => '[removed]',
        '.innerHTML'        => '[removed]',
        '-moz-binding'      => '[removed]',
        '<!--'              => '&lt;!--',
        '-->'               => '--&gt;',
        '<![CDATA['         => '&lt;![CDATA[',
        '<comment>'	        => '&lt;comment&gt;',
        '<%'                => '&lt;&#37;'
    ];
    
    protected static $neverAllowedRegex = [
        'javascript\s*:',
        '(\(?document\)?|\(?window\)?(\.document)?)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    ];
    
    protected static $filenameBadChars = [
        '../', '<!--', '-->', '<', '>',
        "'", '"', '&', '$', '#',
        '{', '}', '[', ']', '=',
        ';', '?', '%20', '%22',
        '%3c',		// <
        '%253c',	// <
        '%3e',		// >
        '%0e',		// >
        '%28',		// (
        '%29',		// )
        '%2528',	// (
        '%26',		// &
        '%24',		// $
        '%3f',		// ?
        '%3b',		// ;
        '%3d'		// =
    ];
    
    /**
     * Sanitizes data so that Cross Site Scripting Hacks can be
	 * prevented. 
	 * 
	 * The credit for this method and its supporting methods 
	 * goes towards codeignitor.
     * 
     * @param string|array $str
     * @param boolean $isImage
     * @return boolean|string
     */
    public static function xssClean($str, $isImage = false)
    {
      
        // Is the string an array?
        if (is_array($str)) {
            foreach ($str as $key => &$value) {
                $str[$key] = self::xssClean($value);
            }
            return $str;
        }
        
        
        // Remove Invisible Characters
        $str = self::removeInvisibleCharacters($str);
        
        /*
         * URL Decode
         *
         * Just in case stuff like this is submitted:
         *
         * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
         *
         * Note: Use rawurldecode() so it does not remove plus signs
         */
        if (stripos($str, '%') !== false) {
            do {
                $oldstr = $str;
                $str = rawurldecode($str);
                $str = preg_replace_callback('#%(?:\s*[0-9a-f]){2,}#i', [SecurityHelper::class, 'urlDecodeSpaces'], $str);
            }
            
            while ($oldstr !== $str);
            unset($oldstr);
        }
        
        /*
         * Convert character entities to ASCII
         *
         * This permits our tests below to work reliably.
         * We only convert entities that are within tags since
         * these are the ones that will pose security problems.
         */
        $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", [SecurityHelper::class, 'convertAttribute'], $str);
        $str = preg_replace_callback('/<\w+.*/si', [SecurityHelper::class, 'decodeEntity'], $str);
        
        // Remove Invisible Characters Again!
        $str = self::removeInvisibleCharacters($str);
        
        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: ja	vascript
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */
        $str = str_replace("\t", ' ', $str);
        
        // Capture converted string for later comparison
        $convertedString = $str;
        
        // Remove Strings that are never allowed
        $str = self::doNeverAllowed($str);
        
        /*
         * Makes PHP tags safe
         *
         * Note: XML tags are inadvertently replaced too:
         *
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        if ($isImage) {
            // Images have a tendency to have the PHP short opening and
            // closing tags every so often so we skip those and only
            // do the long opening tags.
            $str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
        } else {
            $str = str_replace(['<?', '?'.'>'], ['&lt;?', '?&gt;'], $str);
        }
        
        /*
         * Compact any exploded words
         *
         * This corrects words like:  j a v a s c r i p t
         * These words are compacted back to their correct state.
         */
        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt', 'eval'
        ];
        foreach ($words as $word)
        {
            $word = implode('\s*', str_split($word)).'\s*';
            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = preg_replace_callback('#('.substr($word, 0, -3).')(\W)#is', [SecurityHelper::class, 'compactExplodedWords'], $str);
        }
        
        /*
         * Remove disallowed Javascript in links or img tags
         * We used to do some version comparisons and use of stripos(),
         * but it is dog slow compared to these simplified non-capturing
         * preg_match(), especially if the pattern exists in the string
         *
         * Note: It was reported that not only space characters, but all in
         * the following pattern can be parsed as separators between a tag name
         * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
         * ... however, remove_invisible_characters() above already strips the
         * hex-encoded ones, so we'll skip them below.
         */
        do
        {
            $original = $str;
            if (preg_match('/<a/i', $str)) {
                $str = preg_replace_callback('#<a(?:rea)?[^a-z0-9>]+([^>]*?)(?:>|$)#si', [SecurityHelper::class, 'jsLinkRemoval'], $str);
            }
            
            if (preg_match('/<img/i', $str)) {
                $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', [SecurityHelper::class, 'jsImgRemoval'], $str);
            }
            
            if (preg_match('/script|xss/i', $str)) {
                $str = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $str);
            }
        }
        while ($original !== $str);
        unset($original);
        
        /*
         * Sanitize naughty HTML elements
         *
         * If a tag containing any of the words in the list
         * below is found, the tag gets converted to entities.
         *
         * So this: <blink>
         * Becomes: &lt;blink&gt;
         */
        $pattern = '#'
            .'<((?<slash>/*\s*)((?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)|.+)' // tag start and name, followed by a non-tag character
            .'[^\s\042\047a-z0-9>/=]*' // a valid attribute character immediately after the tag would count as a separator
            // optional attributes
            .'(?<attributes>(?:[\s\042\047/=]*' // non-attribute characters, excluding > (tag close) for obvious reasons
            .'[^\s\042\047>/=]+' // attribute characters
            // optional attribute-value
            .'(?:\s*=' // attribute-value separator
            .'(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*))' // single, double or non-quoted value
            .')?' // end optional attribute-value group
            .')*)' // end optional attributes group
            .'[^>]*)(?<closeTag>\>)?#isS';
        
        // Note: It would be nice to optimize this for speed, BUT
        //       only matching the naughty elements here results in
        //       false positives and in turn - vulnerabilities!
        do
        {
            $old_str = $str;
            $str = preg_replace_callback($pattern, [SecurityHelper::class, 'sanitizeNaughtyHtml'], $str);
        }
        while ($old_str !== $str);
        unset($old_str);
        
        /*
         * Sanitize naughty scripting elements
         *
         * Similar to above, only instead of looking for
         * tags it looks for PHP and JavaScript commands
         * that are disallowed. Rather than removing the
         * code, it simply converts the parenthesis to entities
         * rendering the code un-executable.
         *
         * For example:	eval('some code')
         * Becomes:	eval&#40;'some code'&#41;
         */
        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str
            );
        
        // Same thing, but for "tag functions" (e.g. eval`some code`)
        // See https://github.com/bcit-ci/CodeIgniter/issues/5420
        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)`(.*?)`#si',
            '\\1\\2&#96;\\3&#96;',
            $str
            );
        
        // Final clean up
        // This adds a bit of extra precaution in case
        // something got through the above filters
        $str = self::doNeverAllowed($str);
        
        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        if ($isImage) {
            return ($str === $convertedString);
        }
        return $str;
    }
    
    /**
     * 
     * @param string $str
     * @param boolean $urlEncoded
     * @return mixed
     */
    public static function removeInvisibleCharacters($str, $urlEncoded = true)
    {
        $nonDisplayables = [];
        
        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($urlEncoded) {
            $nonDisplayables[] = '/%0[0-8bcef]/i';  // url encoded 00-08, 11, 12, 14, 15
            $nonDisplayables[] = '/%1[0-9a-f]/i';	// url encoded 16-31
            $nonDisplayables[] = '/%7f/i';          // url encoded 127
        }
        
        $nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127
        
        do {
            $str = preg_replace($nonDisplayables, '', $str, -1, $count);
        } while ($count);
        
        return $str;
    }
        
    /**
     *
     * @return string
     */
    public static function xssHash($store = false)
    {
        
        if ($store) {
            if (!isset(self::$xssHash)) {
                $rand = self::getRandomBytes(16);
                self::$xssHash = (!$rand) ? md5(uniqid(mt_rand(), true)) : bin2hex($rand);
            }
            
            return self::$xssHash;
        } else {
            $rand = self::getRandomBytes(16);
            return (!$rand) ? md5(uniqid(mt_rand(), true)) : bin2hex($rand);
        }
        
    }
    
    /**
     * 
     * @param string $str
     * @param boolean $relative_path
     * @return string
     */
    public static function sanitizeFilename($str, $relativePath = false)
    {
        $bad = self::$filenameBadChars;
        if (!$relativePath) {
            $bad[] = './';
            $bad[] = '/';
        }
        
        $str = self::removeInvisibleCharacters($str, false);
        
        do {
            $old = $str;
            $str = str_replace($bad, '', $str);
        } while ($old !== $str);
        
        return stripslashes($str);
    }
    
    /**
     *
     * @param int $length
     * @return boolean|string
     */
    public static function getRandomBytes($length)
    {
        if (empty($length) || !ctype_digit((string) $length)) {
            return false;
        }
        
        if (function_exists('random_bytes')) {
            try {
                // The cast is required to avoid TypeError
                return random_bytes((int) $length);
            } catch (\Exception $e) {
                // If random_bytes() can't do the job, we can't either ...
                // There's no point in using fallbacks.
                error_log($e->getMessage());
                return false;
            }
        }
        
        // Unfortunately, none of the following PRNGs is guaranteed to exist ...
        if (defined('MCRYPT_DEV_URANDOM') && ($output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) !== false) {
            return $output;
        }
        
        if (is_readable('/dev/urandom') && ($fp = fopen('/dev/urandom', 'rb')) !== false) {
            // Try not to waste entropy ...
            stream_set_chunk_size($fp, $length);
            $output = fread($fp, $length);
            fclose($fp);
            if ($output !== false) {
                return $output;
            }
        }
        
        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }
        
        return false;
    }
    
    /**
     * 
     * @param array $matches
     * @return string
     */
    private static function urlDecodeSpaces($matches)
    {
        $input = $matches[0];
        $nospaces = preg_replace('#\s+#', '', $input);
        return ($nospaces === $input) ? $input : rawurldecode($nospaces);
    }
    
    /**
     * 
     * @param array $match
     * @return mixed
     */
    private static function convertAttribute(array $match)
    {
        return str_replace(['>', '<', '\\'], ['&gt;', '&lt;', '\\\\'], $match[0]);
    }
    
    /**
     * 
     * @param array $match
     * @return mixed
     */
    private static function decodeEntity(array $match)
    {
        // Protect GET variables in URLs
        // 901119URL5918AMP18930PROTECT8198
        $match = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', self::xssHash(true).'\\1=\\2', $match[0]);
        // Decode, then un-protect URL GET vars
        return str_replace(self::xssHash(true), '&', self::htmlEntityDecode($match, self::$charset));
    }
    
    /**
     * 
     * @param string $str
     * @return string
     */
    private static function doNeverAllowed($str)
    {
        $str = str_replace(array_keys(self::$neverAllowedStr), self::$neverAllowedStr, $str);
        foreach (self::$neverAllowedRegex as $regex) {
            $str = preg_replace('#'.$regex.'#is', '[removed]', $str);
        }
        return $str;
    }

    /**
     *
     * @param array $match
     * @return string
     */
    private static function compactExplodedWords(array $matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
    }
    
    /**
     *
     * @param array $match
     * @return string
     */
    private static function jsLinkRemoval(array $match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;|`|&\#96;)|javascript:|livescript:|mocha:|charset=|window\.|\(?document\)?\.|\.cookie|<script|<xss|d\s*a\s*t\s*a\s*:)#si',
                '',
                self::filterAttributes($match[1])
                ),
            $match[0]
            );
    }
    
    /**
     *
     * @param array $match
     * @return string
     */
    private static function jsImgRemoval(array $match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#src=.*?(?:(?:alert|prompt|confirm|eval)(?:\(|&\#40;|`|&\#96;)|javascript:|livescript:|mocha:|charset=|window\.|\(?document\)?\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                self::filterAttributes($match[1])
                ),
            $match[0]
            );
    }

    /**
     *
     * @param string $str
     * @return string
     */
    private static function filterAttributes($str)
    {
        $out = '';
        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace('#/\*.*?\*/#s', '', $match);
            }
        }
        return $out;
    }
    
    /**
     *
     * @param array $match
     * @return string
     */
    private static function sanitizeNaughtyHtml(array $matches)
    {
        
        // First, escape unclosed tags
        if (empty($matches['closeTag'])) {
            return '&lt;'.$matches[1];
        }
        // Is the element that we caught naughty? If so, escape it
        else if (in_array(strtolower($matches['tagName']), self::$naughtyTags, true)) {
            return '&lt;'.$matches[1].'&gt;';
        }
        // For other tags, see if their attributes are "evil" and strip those
        else if (isset($matches['attributes'])) {
            // We'll store the already filtered attributes here
            $attributes = [];
            
            // Blacklist pattern for evil attribute names
            $isEvilPattern = '#^('.implode('|', self::$evilAttributes).')$#i';
            // Each iteration filters a single attribute
            do {
                // Strip any non-alpha characters that may precede an attribute.
                // Browsers often parse these incorrectly and that has been a
                // of numerous XSS issues we've had.
                $matches['attributes'] = preg_replace('#^[^a-z]+#i', '', $matches['attributes']);
                if ( ! preg_match(self::$attributesPattern, $matches['attributes'], $attribute, PREG_OFFSET_CAPTURE)) {
                    // No (valid) attribute found? Discard everything else inside the tag
                    break;
                }
                
                if (
                    // Is it indeed an "evil" attribute?
                    preg_match($isEvilPattern, $attribute['name'][0])
                    // Or does it have an equals sign, but no value and not quoted? Strip that too!
                    || (trim($attribute['value'][0]) === '')
                    ) {
                    $attributes[] = 'xss=removed';
                } else {
                    $attributes[] = $attribute[0][0];
                }
                $matches['attributes'] = substr($matches['attributes'], $attribute[0][1] + strlen($attribute[0][0]));
            } while ($matches['attributes'] !== '');
            
            $attributes = empty($attributes) ? '' : ' '.implode(' ', $attributes);
            return '<'.$matches['slash'].$matches['tagName'].$attributes.'>';
        }
        
        return $matches[0];
    }
    
    /**
     * Replacement for html_entity_decode()
     *
     * @param string $str
     * @param string $charset
     * @return string
     */
    public static function htmlEntityDecode($str, $charset = null)
    {
        if (strpos($str, '&') === false) {
            return $str;
        }
        
        static $entities;
        isset($charset)   || $charset = self::$charset;
        isset($entities) || $entities = array_map('strtolower', get_html_translation_table(HTML_ENTITIES, ENT_COMPAT | ENT_HTML5, $charset));
        
        do {
            $str_compare = $str;
            // Decode standard entities, avoiding false positives
            if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
                $replace = array();
                $matches = array_unique(array_map('strtolower', $matches[0]));
                foreach ($matches as &$match) {
                    if (($char = array_search($match.';', $entities, true)) !== false) {
                        $replace[$match] = $char;
                    }
                }
                $str = str_replace(array_keys($replace), array_values($replace), $str);
            }
            // Decode numeric & UTF16 two byte entities
            $str = html_entity_decode(
                preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                ENT_COMPAT | ENT_HTML5,
                $charset
                );
        } while ($str_compare !== $str);
        
        return $str;
    }
    
}