<?php
namespace wiggum\tests\helpers;

use PHPUnit\Framework\TestCase;
use wiggum\commons\helpers\InflectorHelper;

class HelperInflectorTest extends TestCase
{
        
    public function testSingular()
    {
        $strs = [
            'tellies'      => 'telly',
            'smellies'     => 'smelly',
            'abjectnesses' => 'abjectness',
            'smells'       => 'smell',
            'equipment'    => 'equipment'
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, InflectorHelper::singular($str));
        }
        
    }
    
    public function testPlural()
    {
        $strs = [
            'telly'      => 'tellies',
            'smelly'     => 'smellies',
            'abjectness' => 'abjectnesses', // ref : https://en.wiktionary.org/wiki/abjectnesses
            'smell'      => 'smells',
            'witch'      => 'witches',
            'equipment'  => 'equipment'
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, InflectorHelper::plural($str));
        }
    }
  
    public function testOrdinalFormat()
    {
        
        $strs = [
            1                => '1st',
            2                => '2nd',
            4                => '4th',
            11               => '11th',
            12               => '12th',
            13               => '13th',
            'something else' => 'something else',
        ];
        
        foreach ($strs as $str => $expect) {
            $this->assertEquals($expect, InflectorHelper::ordinalFormat($str));
        }
    }
    
}