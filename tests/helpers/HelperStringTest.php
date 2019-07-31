<?php
namespace wiggum\tests\helpers;

use PHPUnit\Framework\TestCase;
use wiggum\commons\helpers\StringHelper;

class HelperStringTest extends TestCase
{
    
    public function testStartsWith()
    {
        $this->assertTrue(StringHelper::startsWith('jason', 'jas'));
        $this->assertTrue(StringHelper::startsWith('jason', 'jason'));
        $this->assertTrue(StringHelper::startsWith('jason', 'jas'));
        $this->assertFalse(StringHelper::startsWith('jason', 'day'));
        $this->assertFalse(StringHelper::startsWith('jason', 'day'));
        $this->assertFalse(StringHelper::startsWith('jason', ''));
        $this->assertFalse(StringHelper::startsWith('7', ' 7'));
        $this->assertTrue(StringHelper::startsWith('7a', '7'));
        $this->assertTrue(StringHelper::startsWith('7a', 7));
        $this->assertTrue(StringHelper::startsWith('7.12a', 7.12));
        $this->assertFalse(StringHelper::startsWith('7.12a', 7.13));
        $this->assertTrue(StringHelper::startsWith(7.123, '7'));
        $this->assertTrue(StringHelper::startsWith(7.123, '7.12'));
        $this->assertFalse(StringHelper::startsWith(7.123, '7.13'));
        // Test for multibyte string support
        $this->assertTrue(StringHelper::startsWith('Jönköping', 'Jö'));
        $this->assertTrue(StringHelper::startsWith('Malmö', 'Malmö'));
        $this->assertFalse(StringHelper::startsWith('Jönköping', 'Jonko'));
        $this->assertFalse(StringHelper::startsWith('Malmö', 'Malmo'));
    }
        
    public function testEndsWith()
    {
        $this->assertTrue(StringHelper::endsWith('jason', 'on'));
        $this->assertTrue(StringHelper::endsWith('jason', 'jason'));
        $this->assertTrue(StringHelper::endsWith('jason', 'on'));
        $this->assertFalse(StringHelper::endsWith('jason', 'no'));
        $this->assertFalse(StringHelper::endsWith('jason', 'no'));
        $this->assertFalse(StringHelper::endsWith('jason', ''));
        $this->assertFalse(StringHelper::endsWith('7', ' 7'));
        $this->assertTrue(StringHelper::endsWith('a7', '7'));
        $this->assertTrue(StringHelper::endsWith('a7', 7));
        $this->assertTrue(StringHelper::endsWith('a7.12', 7.12));
        $this->assertFalse(StringHelper::endsWith('a7.12', 7.13));
        $this->assertTrue(StringHelper::endsWith(0.27, '7'));
        $this->assertTrue(StringHelper::endsWith(0.27, '0.27'));
        $this->assertFalse(StringHelper::endsWith(0.27, '8'));
        // Test for multibyte string support
        $this->assertTrue(StringHelper::endsWith('Jönköping', 'öping'));
        $this->assertTrue(StringHelper::endsWith('Malmö', 'mö'));
        $this->assertFalse(StringHelper::endsWith('Jönköping', 'oping'));
        $this->assertFalse(StringHelper::endsWith('Malmö', 'mo'));
    }
    
    public function testReplaceFirst()
    {
        $this->assertEquals('fooqux foobar', StringHelper::replaceFirst('bar', 'qux', 'foobar foobar'));
        $this->assertEquals('foo/qux? foo/bar?', StringHelper::replaceFirst('bar?', 'qux?', 'foo/bar? foo/bar?'));
        $this->assertEquals('foo foobar', StringHelper::replaceFirst('bar', '', 'foobar foobar'));
        $this->assertEquals('foobar foobar', StringHelper::replaceFirst('xxx', 'yyy', 'foobar foobar'));
        $this->assertEquals('foobar foobar', StringHelper::replaceFirst('', 'yyy', 'foobar foobar'));
        // Test for multibyte string support
        $this->assertEquals('Jxxxnköping Malmö', StringHelper::replaceFirst('ö', 'xxx', 'Jönköping Malmö'));
        $this->assertEquals('Jönköping Malmö', StringHelper::replaceFirst('', 'yyy', 'Jönköping Malmö'));
    }
    
    public function testReplaceLast()
    {
        $this->assertEquals('foobar fooqux', StringHelper::replaceLast('bar', 'qux', 'foobar foobar'));
        $this->assertEquals('foo/bar? foo/qux?', StringHelper::replaceLast('bar?', 'qux?', 'foo/bar? foo/bar?'));
        $this->assertEquals('foobar foo', StringHelper::replaceLast('bar', '', 'foobar foobar'));
        $this->assertEquals('foobar foobar', StringHelper::replaceLast('xxx', 'yyy', 'foobar foobar'));
        $this->assertEquals('foobar foobar', StringHelper::replaceLast('', 'yyy', 'foobar foobar'));
        // Test for multibyte string support
        $this->assertEquals('Malmö Jönkxxxping', StringHelper::replaceLast('ö', 'xxx', 'Malmö Jönköping'));
        $this->assertEquals('Malmö Jönköping', StringHelper::replaceLast('', 'yyy', 'Malmö Jönköping'));
    }
    
    public function testIncrementString()
    {
        $this->assertEquals('my-test-1', StringHelper::incrementString('my-test'));
        $this->assertEquals('my-test_1', StringHelper::incrementString('my-test', '_'));
        $this->assertEquals('file-5', StringHelper::incrementString('file-4'));
        $this->assertEquals('file_5', StringHelper::incrementString('file_4', '_'));
        $this->assertEquals('file-1', StringHelper::incrementString('file', '-', '1'));
        $this->assertEquals(124, StringHelper::incrementString('123', ''));
    }
    
    public function testRandomString()
    {
        $this->assertEquals(16, strlen(StringHelper::randomString('alnum', 16)));
        $this->assertIsString(StringHelper::randomString('numeric', 16));
    }
    
    public function testReduceMultiples()
    {
        $strs = [
            'Fred, Bill,, Joe, Jimmy'	=> 'Fred, Bill, Joe, Jimmy',
            'Ringo, John, Paul,,'		=> 'Ringo, John, Paul,'
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, StringHelper::reduceMultiples($str));
        }
        
        $strs = [
            'Fred, Bill,, Joe, Jimmy'	=> 'Fred, Bill, Joe, Jimmy',
            'Ringo, John, Paul,,'		=> 'Ringo, John, Paul'
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, StringHelper::reduceMultiples($str, ',', true));
        }
    }
    
}