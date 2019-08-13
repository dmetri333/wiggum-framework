<?php
namespace wiggum\commons\helpers;

class DownloadHelper
{
    
    /**
     *
     * @param string $filename
     * @param string $data
     * @param string $setMime
     */
    public static function forceFile($filename, $setMime = false, $charset = 'UTF-8')
    {
        if ($filename === '') {
            return;
        }
        
        $filepath = $filename;
        $filename = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $filename));
        $filename = end($filename);
        
        if (!@is_file($filepath) || ($filesize = @filesize($filepath)) === false) {
            return;
        }
        
        // Set the default MIME type to send
        $mime = 'application/octet-stream';
        
        $x = explode('.', $filename);
        $extension = end($x);
        
        /*
         //TODO fast way to look up mimes
         if ($setMime) {
         if (count($x) === 1 || $extension === '') {
         return;
         }
         
         // Load the mime types
         $mimes =& get_mimes();
         
         // Only change the default MIME if we can find one
         if (isset($mimes[$extension])) {
         $mime = is_array($mimes[$extension]) ? $mimes[$extension][0] : $mimes[$extension];
         }
         }
         */
        
        /* It was reported that browsers on Android 2.1 (and possibly older as well)
         * need to have the filename extension upper-cased in order to be able to
         * download it.
         *
         * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
         */
        if (count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT'])) {
            $x[count($x) - 1] = strtoupper($extension);
            $filename = implode('.', $x);
        }
        
        // Clean output buffer
        if (ob_get_level() !== 0 && @ob_end_clean() === false) {
            @ob_clean();
        }
        
        // RFC 6266 allows for multibyte filenames, but only in UTF-8,
        // so we have to make it conditional ...
        $utf8Filename = ($charset !== 'UTF-8') ? self::convertToUtf8($filename, $charset) : $filename;
        isset($utf8Filename[0]) && $utf8Filename = " filename*=UTF-8''".rawurlencode($utf8Filename);
        
        // Generate the server headers
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$filename.'";'.$utf8Filename);
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$filesize);
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        
        // Flush the file
        if (@readfile($filepath) === false) {
            return;
        }
        
        exit;
    }
    
    /**
     *
     * @param string $filename
     * @param string $data
     * @param string $setMime
     */
    public static function forceData($filename, $data, $setMime = false, $charset = 'UTF-8')
    {
        if ($filename === '' || empty($data)) {
            return;
        } 
        
        $filesize = strlen($data);
        
        // Set the default MIME type to send
        $mime = 'application/octet-stream';
        
        $x = explode('.', $filename);
        $extension = end($x);
        
        /*
        //TODO fast way to look up mimes
        if ($setMime) {
            if (count($x) === 1 || $extension === '') {
                return;
            }
            
            // Load the mime types
            $mimes =& get_mimes();
            
            // Only change the default MIME if we can find one
            if (isset($mimes[$extension])) {
                $mime = is_array($mimes[$extension]) ? $mimes[$extension][0] : $mimes[$extension];
            }
        }
        */
        
        /* It was reported that browsers on Android 2.1 (and possibly older as well)
         * need to have the filename extension upper-cased in order to be able to
         * download it.
         *
         * Reference: http://digiblog.de/2011/04/19/android-and-the-download-file-headers/
         */
        if (count($x) !== 1 && isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Android\s(1|2\.[01])/', $_SERVER['HTTP_USER_AGENT'])) {
            $x[count($x) - 1] = strtoupper($extension);
            $filename = implode('.', $x);
        }
        
        // Clean output buffer
        if (ob_get_level() !== 0 && @ob_end_clean() === false) {
            @ob_clean();
        }
        
        // RFC 6266 allows for multibyte filenames, but only in UTF-8,
        // so we have to make it conditional ...
        $utf8Filename = ($charset !== 'UTF-8') ? self::convertToUtf8($filename, $charset) : $filename;
        isset($utf8Filename[0]) && $utf8Filename = " filename*=UTF-8''".rawurlencode($utf8Filename);
        
        // Generate the server headers
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$filename.'";'.$utf8Filename);
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$filesize);
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        
        // raw data - just dump it
        exit($data);
        
        exit;
    }
    
    /**
     * 
     * @param string $str
     * @param string $encoding
     * @return string|boolean
     */
    private static function convertToUtf8($str, $encoding)
    {
        if (extension_loaded('mbstring')) {
            return mb_convert_encoding($str, 'UTF-8', $encoding);
        } else if (extension_loaded('iconv')) {
            return @iconv($encoding, 'UTF-8', $str);
        }
        
        return false;
    }
    
}
