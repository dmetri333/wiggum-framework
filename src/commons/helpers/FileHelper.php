<?php
namespace wiggum\commons\helpers;

class FileHelper
{
    
    /**
     *
     * @param string $path
     * @param string $data
     * @param string $mode
     * @return boolean
     */
    public static function write(string $path, string $data, string $mode = 'wb'): bool
    {
        if (!$fp = @fopen($path, $mode)) {
            return false;
        }
        
        flock($fp, LOCK_EX);
        
        for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result) {
            if (($result = fwrite($fp, substr($data, $written))) === false) {
                break;
            }
        }
        
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return is_int($result);
    }
    
    /**
     * 
     * @param string $path
     * @param boolean $recursive
     * @param int $mode
     * @return boolean
     */
    public static function createFolder(string $path, bool $recursive = true, int $mode = 0770): bool
    {
        if (file_exists($path)) {
            return true;
        } else {
            $result = mkdir($path, $mode, $recursive);
            if (!$result) {
                return false;
            }
        }
        return true;
    }
    
    
    /**
     *
     * @param string $source
     * @param string $target
     * @return boolean
     */
    public static function move(string $source, string $target): bool
    {
        if (file_exists($target)) {
            return false;
        }
        
        if (!file_exists($source)) {
            return false;
        }
        
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }
        
        return rename($source, $target);
    }
    
    /**
     *
     * @param string $source
     * @param string $target
     * @param bool $overwrite
     * @return bool
     */
    public static function copy(string $source, string $target, bool $overwrite = false): bool
    {
        
        if (!file_exists($source)) {
            return false;
        }
        
        if (!$overwrite && file_exists($target)) {
            return false;
        }
        
        if (is_dir($source)) {
            if (!mkdir($target, 0770, true)) {
                return false;
            }
            
            $directoryIterator = new \DirectoryIterator($source);
            foreach ($directoryIterator as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                
                if ($fileInfo->isDir()) {
                    $result = self::copy($fileInfo->getPathname(), $target . DIRECTORY_SEPARATOR . $fileInfo->getFilename(), $overwrite);
                } elseif ($fileInfo->isFile()) {
                    $result = copy($fileInfo->getPathname(), $target . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
                }
            }
            return $result;
        }
        
        return copy($source, $target);
    }
    
    /**
     * 
     * @param string $source
     * @param bool $recursive
     * @return bool
     */
    public static function delete(string $source, bool $recursive = false): bool
    {
        if (!file_exists($source)) {
            return false;
        }
        
        if (is_file($source)) {
            return unlink($source);
        }
        
        if (is_dir($source)) {
            if ($recursive) {
                $directoryIterator = new \DirectoryIterator($source);
                foreach ($directoryIterator as $fileInfo) {
                    if ($fileInfo->isDot()) {
                        continue;
                    }
                    
                    self::delete($fileInfo->getPathname(), $recursive);
                }
            }
            
            return rmdir($source);
        }
        
        return false;
    }
    
    /**
     * 
     * @param string $source
     * @param int $depth
     * @param bool $hidden
     * @return boolean|array
     */
    public static function directoryList(string $source, int $depth = 0, bool $hidden = false)
    {
        if ($fp = @opendir($source)) {
            
            $filedata = [];
            $newDepth = $depth - 1;
            $source	= rtrim($source, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            
            while (false !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.')) {
                    continue;
                }
                
                is_dir($source.$file) && $file .= DIRECTORY_SEPARATOR;
                
                if (is_dir($source . $file)) {
                    $info = self::fileInfo($source . $file);
                    $info['file'] = false;
                    if ($depth < 1 || $newDepth > 0) {
                        $info['children'] = self::directoryList($source . $file, $newDepth, $hidden);
                    }
                    $filedata[] = $info;
                } else {
                    $info = self::fileInfo($source . $file);
                    $info['file'] = true;
                    $filedata[] =  $info;
                }
            }
            
            closedir($fp);
            return $filedata;
        }
        
        return false;
    }
    
    /**
     * 
     * @param string $source
     * @return int
     */
    public static function size(string $source): int
    {
        
        if (is_file($source)) {
            return filesize($source);
        }
        
        $size = 0;
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source)) as $file) {
            if ($file->isDot()) {
                continue;
            }
            
            $size += $file->getSize();
        }
    
        return $size;
    } 
    
    /**
     *
     * @param string $file
     * @param array $returnedValues
     * @return boolean|array
     */
    public static function fileInfo(string $file, array $returnedValues = ['name', 'path', 'size', 'date'])
    {
        if (!file_exists($file)) {
            return false;
        }
        
        $fileinfo = [];
        
        foreach ($returnedValues as $key) {
            switch ($key) {
                case 'name':
                    $fileinfo['name'] = basename($file);
                    break;
                case 'path':
                    $fileinfo['path'] = $file;
                    break;
                case 'size':
                    $fileinfo['size'] = filesize($file);
                    break;
                case 'date':
                    $fileinfo['date'] = filemtime($file);
                    break;
                case 'readable':
                    $fileinfo['readable'] = is_readable($file);
                    break;
                case 'writable':
                    $fileinfo['writable'] = self::isWritable($file);
                    break;
                case 'executable':
                    $fileinfo['executable'] = is_executable($file);
                    break;
                case 'fileperms':
                    $fileinfo['fileperms'] = fileperms($file);
                    break;
            }
        }
        
        return $fileinfo;
    }
    
    /**
     * 
     * @param int $bytes
     * @return string
     */
    public static function formatBytes(int $bytes): string
    {
        $result = (float) $bytes;
        if ($bytes >= 1048576) {
            $result = (ceil($bytes / 1048576)*100 / 100) . 'MB';
        } elseif ($bytes >= 1024) {
            $result = ceil($bytes / 1024) . 'KB';
        } else {
            $result = $bytes . ' bytes';
        }
        return $result;
    }
    
    /**
     *
     * @param string $filename
     * @return string
     */
    public static function extension(string $filename): string
    {
        $x = explode('.', $filename);
        
        if (count($x) === 1) {
            return '';
        }
        
        return strtolower(end($x));
    }
    
    /**
     * 
     * @param string $file
     * @param string $default
     * @return string
     */
    public static function mimeType(string $file, string $default = ''): string
    {
        $fileType = self::detectMimeType($file);
        $fileType = empty($fileType) ? $default : $fileType;
        
        $fileType = preg_replace('/^(.+?);.*$/', '\\1', $fileType);
        $fileType = strtolower(trim(stripslashes($fileType), '"'));
            
        // IE will sometimes return odd mime-types during upload, so here we just standardize all
        // jpegs or pngs to the same file type.
        if (in_array($fileType, ['image/x-png'])) {
            $fileType = 'image/png';
        } else if (in_array($fileType, ['image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg'])) {
            $fileType = 'image/jpeg';
        }
        
        return $fileType;
    }
    
    /**
     * 
     * @param string $file
     * @return mixed
     */
    private static function detectMimeType(string $file)
    {
        // We'll need this to validate the MIME info string (e.g. text/plain; charset=us-ascii)
        $regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';
        
        /**
         * Fileinfo extension - most reliable method
         *
         * Apparently XAMPP, CentOS, cPanel and who knows what
         * other PHP distribution channels EXPLICITLY DISABLE
         * ext/fileinfo, which is otherwise enabled by default
         * since PHP 5.3 ...
         */
        if (function_exists('finfo_file'))
        {
            $finfo = @finfo_open(FILEINFO_MIME);
            if (is_resource($finfo)) // It is possible that a FALSE value is returned, if there is no magic MIME database file found on the system
            {
                $mime = @finfo_file($finfo, $file);
                finfo_close($finfo);
                
                /* According to the comments section of the PHP manual page,
                 * it is possible that this function returns an empty string
                 * for some files (e.g. if they don't exist in the magic MIME database)
                 */
                if (is_string($mime) && preg_match($regexp, $mime, $matches))
                {
                    return $matches[1];
                }
            }
        }
        
        // Fall back to mime_content_type(), if available (still better than $_FILES[$field]['type'])
        if (function_exists('mime_content_type'))
        {
            $fileType = @mime_content_type($file);
            if (strlen($fileType) > 0) // It's possible that mime_content_type() returns FALSE or an empty string
            {
                return $fileType;
            }
        }
        
        return false;
    }
    
    /**
     *
     * @param string $file
     * @return bool
     */
    public static function isWritable(string $file): bool
    {
        // If we're on a UNIX-like server, just is_writable()
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        
        /* For Windows servers and safe_mode "on" installations we'll actually
         * write a file then read it.
         */
        if (is_dir($file)) {
            $file = rtrim($file, '/').'/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } else if (!is_file($file) || ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        
        fclose($fp);
        return true;
    }

}
