<?php
namespace wiggum\tests\helpers;

use PHPUnit\Framework\TestCase;
use wiggum\services\helpers\Helpers;
use wiggum\foundation\Application;
use wiggum\commons\helpers\URLHelper;

class HelperUrlTest extends TestCase
{
       
    public function testSlug()
    {
        $words = [
            'foo bar /' 	=> 'foo-bar',
            '\  testing 12' => 'testing-12'
        ];
        
        foreach ($words as $in => $out) {
            $this->assertEquals($out, URLHelper::slug($in));
        }
    }
    
    public function testSlugExtraDashes()
    {
        $words = [
            '_foo bar_' 	=> 'foo_bar',
            '_What\'s wrong with CSS?_' => 'whats_wrong_with_css'
        ];
        
        foreach ($words as $in => $out) {
            $this->assertEquals($out, URLHelper::slug($in, '_'));
        }
    }
    
    public function testAutoLinkUrl()
    {
        $strings = [
            'www.codeigniter.com test' => '<a href="http://www.codeigniter.com">www.codeigniter.com</a> test',
            'This is my noreply@codeigniter.com test' => 'This is my <a href="mailto:noreply@codeigniter.com">noreply@codeigniter.com</a> test',
            '<br />www.google.com' => '<br /><a href="http://www.google.com">www.google.com</a>',
            'Download CodeIgniter at www.codeigniter.com. Period test.' => 'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>. Period test.',
            'Download CodeIgniter at www.codeigniter.com, comma test' => 'Download CodeIgniter at <a href="http://www.codeigniter.com">www.codeigniter.com</a>, comma test',
            'This one: ://codeigniter.com must not break this one: http://codeigniter.com' => 'This one: <a href="://codeigniter.com">://codeigniter.com</a> must not break this one: <a href="http://codeigniter.com">http://codeigniter.com</a>',
            'Trailing slash: https://codeigniter.com/ fubar' => 'Trailing slash: <a href="https://codeigniter.com/">https://codeigniter.com/</a> fubar',
            '<br />www.google.com' => '<br /><a href="http://www.google.com">www.google.com</a>',
            'this is some text that includes www.email@domain.com which is causing an issue' => 'this is some text that includes <a href="mailto:www.email@domain.com">www.email@domain.com</a> which is causing an issue'
        ];
        
        foreach ($strings as $in => $out) {
            $this->assertEquals($out, URLHelper::autoLink($in));
        }
    }

}