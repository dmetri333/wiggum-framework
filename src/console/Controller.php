<?php
namespace wiggum\console;

abstract class Controller extends \wiggum\foundation\Controller
{
    
    /**
     * 
     * @param array $args
     */
    public abstract function handle(array $args);
    
}