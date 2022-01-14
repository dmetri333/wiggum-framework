<?php
namespace wiggum\commons\helpers;

use \ZipArchive;

class ArchiveHelper
{
    
    /**
     * 
     * @param string $filename
     * @param array $files
     * @throws \Exception
     * @return boolean
     */
    public static function zip($filepath, array $files)
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('ArchiveHelper: ZipArchive is required php package');
        }
        
        if (!is_dir(dirname($filepath))) {
            return false;
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result !== true) {
            return false;
        }
        
        foreach ($files as $file) {
            if (substr($file, 0, 4) === 'http') {
                $contents = file_get_contents($file);
                !empty($contents) && $zip->addFromString(basename($file), $contents);
            } else {
                is_readable($file) && $zip->addFile($file, basename($file));
            }
            
        }
        
        return $zip->close();
    }
    
    /**
     * 
     * @param string $source
     * @param string $target
     * @throws \Exception
     * @return boolean
     */
    public static function unzip($source, $target)
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('ArchiveHelper: ZipArchive is required php package');
        }
        
        if (!is_file($source)) {
            return false;
        }
        
        if (!is_dir($target)) {
            return false;
        }
            
        $zip = new ZipArchive();
        $result = $zip->open($source);
        if ($result !== true) {
            return false;
        }
            
        $zip->extractTo($target);
        
        return $zip->close();
    }

}
