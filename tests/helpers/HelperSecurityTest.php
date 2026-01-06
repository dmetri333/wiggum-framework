<?php
namespace wiggum\tests\helpers;

use PHPUnit\Framework\TestCase;
use wiggum\commons\helpers\SecurityHelper;

class HelperSecurityTest extends TestCase
{
    
    public function testXssClean()
    {
        $result = SecurityHelper::xssClean("Hello, i try to <script>alert('Hack');</script> your site");
        $this->assertEquals("Hello, i try to [removed]alert&#40;'Hack'&#41;;[removed] your site", $result);
    }
   
    public function testXssCleanStringArray()
    {
        $harmStrings = [
            "Hello, i try to <script>alert('Hack');</script> your site",
            "Simple clean string",
            "Hello, i try to <script>alert('Hack');</script> your site"
        ];
        
        $result = SecurityHelper::xssClean($harmStrings);
        $this->assertEquals("Hello, i try to [removed]alert&#40;'Hack'&#41;;[removed] your site", $result[0]);
        $this->assertEquals("Simple clean string", $result[1]);
        $this->assertEquals("Hello, i try to [removed]alert&#40;'Hack'&#41;;[removed] your site", $result[2]);
    }
    
    public function testXssCleanImageValid()
    {
        $xssCleanReturn = SecurityHelper::xssClean('<img src="test.png">', true);
        $this->assertTrue($xssCleanReturn);
    }
    
    public function testXssCleanImageInvalid()
    {
        $result = SecurityHelper::xssClean('<img src=javascript:alert(String.fromCharCode(88,83,83))>', true);
        $this->assertFalse($result);
    }
    
    public function testXssCleanEntityDoubleEncoded()
    {
        $input = '<a href="&#38&#35&#49&#48&#54&#38&#35&#57&#55&#38&#35&#49&#49&#56&#38&#35&#57&#55&#38&#35&#49&#49&#53&#38&#35&#57&#57&#38&#35&#49&#49&#52&#38&#35&#49&#48&#53&#38&#35&#49&#49&#50&#38&#35&#49&#49&#54&#38&#35&#53&#56&#38&#35&#57&#57&#38&#35&#49&#49&#49&#38&#35&#49&#49&#48&#38&#35&#49&#48&#50&#38&#35&#49&#48&#53&#38&#35&#49&#49&#52&#38&#35&#49&#48&#57&#38&#35&#52&#48&#38&#35&#52&#57&#38&#35&#52&#49">Clickhere</a>';
        $result = SecurityHelper::xssClean($input);
        $this->assertEquals('<a>Clickhere</a>', $result);
    }
    
    public function testXssCleanJsLinkRemoval()
    {
        // This one is to prevent a false positive
        $result = SecurityHelper::xssClean("<a href=\"javascrip\n<t\n:alert\n(1)\"\n>");
		$this->assertEquals("<a href=\"javascrip\n<t\n:alert\n&#40;1&#41;\">", $result );
    }
  
    public function testXssCleanJsImgRemoval()
    {
        $input = '<img src="&#38&#35&#49&#48&#54&#38&#35&#57&#55&#38&#35&#49&#49&#56&#38&#35&#57&#55&#38&#35&#49&#49&#53&#38&#35&#57&#57&#38&#35&#49&#49&#52&#38&#35&#49&#48&#53&#38&#35&#49&#49&#50&#38&#35&#49&#49&#54&#38&#35&#53&#56&#38&#35&#57&#57&#38&#35&#49&#49&#49&#38&#35&#49&#49&#48&#38&#35&#49&#48&#50&#38&#35&#49&#48&#53&#38&#35&#49&#49&#52&#38&#35&#49&#48&#57&#38&#35&#52&#48&#38&#35&#52&#57&#38&#35&#52&#49">Clickhere';
        $result = SecurityHelper::xssClean($input);
        $this->assertEquals('<img>', $result);
    }
    
    public function testXssCleanSanitizeNaughtyHtmlTags()
    {
        $result = SecurityHelper::xssClean('<unclosedTag');
        $this->assertEquals('&lt;unclosedTag', $result);
        
        $result = SecurityHelper::xssClean('<blink>');
        $this->assertEquals('&lt;blink&gt;', $result);
        
        $result = SecurityHelper::xssClean('<fubar>');
        $this->assertEquals('<fubar>', $result);
        
        $result = SecurityHelper::xssClean('<img <svg=""> src="x">');
        $this->assertEquals('<img svg=""> src="x">', $result);
        
        $result = SecurityHelper::xssClean('<img src="b on="<x">on=">"x onerror="alert(1)">');
        $this->assertEquals('<img src="b on=">on=">"x onerror="alert&#40;1&#41;">', $result);
        
        $result = SecurityHelper::xssClean("\n><!-\n<b\n<c d=\"'e><iframe onload=alert(1) src=x>\n<a HREF=\"\">\n");
        $this->assertEquals("\n>&lt;!-\n<b d=\"'e><iframe onload=alert&#40;1&#41; src=x>\n<a HREF=\">\n", $result);
    }

    public function testXssCleanSanitizeNaughtyHtmlAttributes()
    {
        $result = SecurityHelper::xssClean('<foo onAttribute="bar">');
        $this->assertEquals('<foo xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<foo onAttributeNoQuotes=bar>');
        $this->assertEquals('<foo xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<foo onAttributeWithSpaces = bar>');
        $this->assertEquals('<foo xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<foo prefixOnAttribute="bar">');
        $this->assertEquals('<foo prefixOnAttribute="bar">', $result);
        
        $result = SecurityHelper::xssClean('<foo>onOutsideOfTag=test</foo>');
        $this->assertEquals('<foo>onOutsideOfTag=test</foo>', $result);
        
        $result = SecurityHelper::xssClean('onNoTagAtAll = true');
        $this->assertEquals('onNoTagAtAll = true', $result);
        
        $result = SecurityHelper::xssClean('<foo fscommand=case-insensitive>');
        $this->assertEquals('<foo xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<foo seekSegmentTime=whatever>');
        $this->assertEquals('<foo xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<foo bar=">" baz=\'>\' onAfterGreaterThan="quotes">');
        $this->assertEquals('<foo bar=">" baz=\'>\' xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<foo bar=">" baz=\'>\' onAfterGreaterThan=noQuotes>');
        $this->assertEquals('<foo bar=">" baz=\'>\' xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<img src="x" on=""> on=<svg> onerror=alert(1)>');
        $this->assertEquals('<img src="x" on=""> on=&lt;svg&gt; onerror=alert&#40;1&#41;>', $result);
        
        $result = SecurityHelper::xssClean('<img src="on=\'">"<svg> onerror=alert(1) onmouseover=alert(1)>');
        $this->assertEquals('<img src="on=\'">"&lt;svg&gt; onerror=alert&#40;1&#41; onmouseover=alert&#40;1&#41;>', $result);
        
        $result = SecurityHelper::xssClean('<img src="x"> on=\'x\' onerror=``,alert(1)>');
        $this->assertEquals('<img src="x"> on=\'x\' onerror=``,alert&#40;1&#41;>', $result);
        
        $result = SecurityHelper::xssClean('<a< onmouseover="alert(1)">');
        $this->assertEquals('<a xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<img src="x"> on=\'x\' onerror=,xssm()>');
        $this->assertEquals('<img src="x"> on=\'x\' onerror=,xssm()>', $result);
        
        $result = SecurityHelper::xssClean('<image src="<>" onerror=\'alert(1)\'>');
        $this->assertEquals('<image src="<>" xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<b "=<= onmouseover=alert(1)>');
        $this->assertEquals('<b xss=removed>', $result);
        
        $result = SecurityHelper::xssClean('<b a=<=" onmouseover="alert(1),1>1">');
        $this->assertEquals('<b xss=removed xss=removed>1">', $result);
        
        $result = SecurityHelper::xssClean('<b "="< x=" onmouseover=alert(1)//">');
        $this->assertEquals('<b x=" onmouseover=alert&#40;1&#41;//">', $result);
    }

    public function testNaughtyHtmlPlusEvilAttributes()
    {
        $result = SecurityHelper::xssClean('<svg<img > src="x" onerror="location=/javascript/.source+/:alert/.source+/(1)/.source">');
        $this->assertEquals('&lt;svg<img src="x" xss=removed>', $result);
    }

    public function testXssHash()
    {
        $hash = SecurityHelper::xssHash();
		$this->assertMatchesRegularExpression('#^[0-9a-f]{32}$#iS', $hash);
    }

    public function testGetRandomBytes()
    {
        $length = "invalid";
        $this->assertFalse(SecurityHelper::getRandomBytes($length));
        
        $length = 10;
        $this->assertNotEmpty(SecurityHelper::getRandomBytes($length));
    }

    public function testEntityDecode()
    {
        
        $decoded = SecurityHelper::htmlEntityDecode('&lt;div&gt;Hello &lt;b&gt;Booya&lt;/b&gt;&lt;/div&gt;');
        $this->assertEquals('<div>Hello <b>Booya</b></div>', $decoded);
        
        $decoded = SecurityHelper::htmlEntityDecode('colon&colon;');
        $this->assertEquals('colon:', $decoded);
        
        $decoded = SecurityHelper::htmlEntityDecode('NewLine&NewLine;');
        $this->assertEquals("NewLine\n",$decoded);
        
        $decoded = SecurityHelper::htmlEntityDecode('Tab&Tab;');
        $this->assertEquals("Tab\t", $decoded);
        
        $decoded = SecurityHelper::htmlEntityDecode('lpar&lpar;');
        $this->assertEquals("lpar(", $decoded);
        
        $decoded = SecurityHelper::htmlEntityDecode('rpar&rpar;');
        $this->assertEquals("rpar)", $decoded);
        
        $decoded = SecurityHelper::htmlEntityDecode('&foo should not include a semicolon');
        $this->assertEquals('&foo should not include a semicolon', $decoded);
    }

}