<?php
namespace wiggum\http\interfaces;

interface Route {
    
    /**
     * 
     * @return array
     */
    public function getMiddleware() : array;
    
    
    /**
     * 
     * @return array
     */
    public function process() : array;
    
}