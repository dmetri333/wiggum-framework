<?php
namespace wiggum\tests\helpers;

use PHPUnit\Framework\TestCase;
use wiggum\commons\helpers\TextHelper;

class HelperTextTest extends TestCase
{
    
    public function testEllipsize()
    {
        $longString = 'Once upon a time, a framework had no tests.  It sad.  So some nice people began to write tests.  The more time that went on, the happier it became.  Everyone was happy.';

        $this->assertEquals('Once upon a time, a &hellip;', TextHelper::ellipsize($longString, 20));
        $this->assertEquals('Once upon &hellip;was happy.', TextHelper::ellipsize($longString, 20, .5));
        $this->assertEquals('Once upon a time, a &#8230;', TextHelper::ellipsize($longString, 20, 1, '&#8230;'));
        $this->assertEquals('Short', TextHelper::ellipsize('Short', 20));
        $this->assertEquals('Short', TextHelper::ellipsize('Short', 5));
    }
    
    public function testAsciiToEntities()
    {
        $strs = [
            '“‘ “test”'			=> '&#8220;&#8216; &#8220;test&#8221;',
            '†¥¨ˆøåß∂ƒ©˙∆˚¬'	=> '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;'
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, TextHelper::asciiToEntities($str));
        }
    }
    
    public function testEntitiesToAscii()
    {
        $strs = [
            '&#8220;&#8216; &#8220;test&#8221;' => '“‘ “test”',
            '&#8224;&#165;&#168;&#710;&#248;&#229;&#223;&#8706;&#402;&#169;&#729;&#8710;&#730;&#172;' => '†¥¨ˆøåß∂ƒ©˙∆˚¬'
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, TextHelper::entitiesToAscii($str));
        }
    }
    
}