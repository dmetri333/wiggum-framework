<?php
namespace wiggum\commons\helpers;

use \ZipArchive;

class ArchiveHelper
{
    
    /**
     * 
     * @param array $files
     * @param string $target
     * @throws \Exception
     * @return bool
     */
    public static function zip(array $files, string $target): bool
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('ArchiveHelper: ZipArchive is required php package');
        }
        
        if (!is_dir(dirname($target))) {
            return false;
        }
        
        $zip = new ZipArchive();
        $result = $zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($result !== true) {
            return false;
        }
        
        $localNames = [];
        
        foreach ($files as $file) {
            
            $basename = basename($file);
            if (in_array($basename, $localNames)) {
                $name = pathinfo($basename, PATHINFO_FILENAME);
                $ext = pathinfo($basename, PATHINFO_EXTENSION);
                $basename = StringHelper::incrementString($name).'.'.$ext;
            }
            
            if (is_readable($file)) {
                $zip->addFile($file, $basename);
                $localNames[] = $basename;
            }

        }
        
        return $zip->close();
    }
  
    /**
     * 
     * @param string $source
     * @param string $target
     * @throws \Exception
     * @return bool
     */
    public static function unzip(string $source, string $target): bool
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