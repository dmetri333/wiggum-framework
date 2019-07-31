<?php
namespace wiggum\tests\helpers;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use wiggum\commons\helpers\FileHelper;

class FileHelperTest extends TestCase
{
        
    private $root;
    
    public function setUp() : void
    {
        $this->root = vfsStream::setup();
    }
    
    public function testFileInfo()
    {
        $content = 'Jack and Jill went up the mountain to fight a billy goat.';
        $lastModified = time() - 86400;
        
        $file = vfsStream::newFile('my_file.txt', 0777)
            ->withContent($content)
            ->lastModified($lastModified)
            ->at($this->root);
        
        $returnValues = [
            'name'        => 'my_file.txt',
            'path'        => 'vfs://root/my_file.txt',
            'size'        => 57,
            'date'        => $lastModified,
            'readable'    => true,
            'writable'    => true,
            'executable'  => true,
            'fileperms'   => 33279
        ];
        
        $info = FileHelper::fileInfo(
            $file->url(),
            ['name', 'path', 'size', 'date', 'readable', 'writable', 'executable', 'fileperms']
        );

        foreach ($info as $k => $v) {
            $this->assertEquals($returnValues[$k], $v);
        }
    }
    
    public function testFileInfoBad()
    {
        $this->assertFalse(FileHelper::fileInfo('not_a_file'));
    }
    
    public function testWriteFile()
    {
        $content = 'Jack and Jill went up the mountain to fight a billy goat.';
        
        $file = vfsStream::newFile('write.txt', 0777)
            ->withContent('')
            ->lastModified(time() - 86400)
            ->at($this->root);
        
            
       $this->assertTrue(FileHelper::write($file->url(), $content));
    }
    
    
}