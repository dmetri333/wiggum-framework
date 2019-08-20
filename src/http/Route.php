<?php
namespace wiggum\http;

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