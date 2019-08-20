<?php
namespace wiggum\commons\helpers;

class InflectorHelper
{

    /**
     *
     * @param string $str
     * @return string
     */
    public static function singular(string $str) : string
    {
        $result = strval($str);
        if (!self::wordCountable($result)) {
            return $result;
        }
        
        $singularRules = [
            '/(matr)ices$/'		=> '\1ix',
            '/(vert|ind)ices$/'	=> '\1ex',
            '/^(ox)en/'		    => '\1',
            '/(alias)es$/'		=> '\1',
            '/([octop|vir])i$/'	=> '\1us',
            '/(cris|ax|test)es$/'	=> '\1is',
            '/(shoe)s$/'		=> '\1',
            '/(o)es$/'		    => '\1',
            '/(bus|campus)es$/'	=> '\1',
            '/([m|l])ice$/'		=> '\1ouse',
            '/(x|ch|ss|sh)es$/'	=> '\1',
            '/(m)ovies$/'		=> '\1\2ovie',
            '/(s)eries$/'		=> '\1\2eries',
            '/([^aeiouy]|qu)ies$/'	=> '\1y',
            '/([lr])ves$/'		=> '\1f',
            '/(tive)s$/'		=> '\1',
            '/(hive)s$/'		=> '\1',
            '/([^f])ves$/'		=> '\1fe',
            '/(^analy)ses$/'	=> '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/'		=> '\1um',
            '/(p)eople$/'		=> '\1\2erson',
            '/(m)en$/'		    => '\1an',
            '/(s)tatuses$/'		=> '\1\2tatus',
            '/(c)hildren$/'		=> '\1\2hild',
            '/(n)ews$/'		    => '\1\2ews',
            '/(quiz)zes$/'		=> '\1',
            '/([^us])s$/'		=> '\1'
        ];
        
        foreach ($singularRules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }
        
        return $result;
    }
    
    
    /**
     * 
     * @param string $str
     * @return string
     */
    public static function plural(string $str) : string
    {
        $result = strval($str);
        if (!self::wordCountable($result)) {
            return $result;
        }
        
        $pluralRules = [
            '/(quiz)$/'                => '\1zes',      // quizzes
            '/^(ox)$/'                 => '\1\2en',     // ox
            '/([m|l])ouse$/'           => '\1ice',      // mouse, louse
            '/(matr|vert|ind)ix|ex$/'  => '\1ices',     // matrix, vertex, index
            '/(x|ch|ss|sh)$/'          => '\1es',       // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/'       => '\1ies',      // query, ability, agency
            '/(hive)$/'                => '\1s',        // archive, hive
            '/(?:([^f])fe|([lr])f)$/'  => '\1\2ves',    // half, safe, wife
            '/sis$/'                   => 'ses',        // basis, diagnosis
            '/([ti])um$/'              => '\1a',        // datum, medium
            '/(p)erson$/'              => '\1eople',    // person, salesperson
            '/(m)an$/'                 => '\1en',       // man, woman, spokesman
            '/(c)hild$/'               => '\1hildren',  // child
            '/(buffal|tomat)o$/'       => '\1\2oes',    // buffalo, tomato
            '/(bu|campu)s$/'           => '\1\2ses',    // bus, campus
            '/(alias|status|virus)$/'  => '\1es',       // alias
            '/(octop)us$/'             => '\1i',        // octopus
            '/(ax|cris|test)is$/'      => '\1es',       // axis, crisis
            '/s$/'                     => 's',          // no change (compatibility)
            '/$/'                      => 's',
        ];
        
        foreach ($pluralRules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * @param string $word
     * @return bool
     */
    public static function wordCountable(string $word) : bool
    {
        return !in_array(
            strtolower($word), [
                'audio',
                'bison',
                'chassis',
                'compensation',
                'coreopsis',
                'data',
                'deer',
                'education',
                'emoji',
                'equipment',
                'fish',
                'furniture',
                'gold',
                'information',
                'knowledge',
                'love',
                'rain',
                'money',
                'moose',
                'nutrition',
                'offspring',
                'plankton',
                'pokemon',
                'police',
                'rice',
                'series',
                'sheep',
                'species',
                'swine',
                'traffic',
                'wheat'
            ]);
    }
    
    /**
     * 
     * @param string $number
     * @return string
     */
    public static function ordinalFormat(string $number) : string
    {
        if (!ctype_digit((string) $number) || $number < 1) {
            return $number;
        }
        
        $lastDigit = [
            0 => 'th',
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            4 => 'th',
            5 => 'th',
            6 => 'th',
            7 => 'th',
            8 => 'th',
            9 => 'th'
        ];
        
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number.'th';
        }
        
        return $number.$lastDigit[$number % 10];
    }

}