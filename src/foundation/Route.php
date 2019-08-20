<?php
namespace wiggum\foundation;

abstract class Route {
    
    /**
     * 
     * @return array
     */
    abstract public function getMiddleware() : array;
    
    
    /**
     * 
     * @return array
     */
    abstract public function process() : array;
    
}