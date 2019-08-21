<?php
namespace wiggum\console;

use wiggum\foundation\Application;

abstract class Command {
    
    protected $app;
    
    /**
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Calling a non-existant var on Controller checks to see if there's an item
     * in the container that is callable and if so, calls it.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($this->app->getContainer()->offsetExists($name)) {
            $obj = $this->app->getContainer()->offsetGet($name);
            return $obj;
        }
        
        throw new \Exception('Unrecognized property ' . $name);
    }
    
    /**
     * 
     * @param array $args
     */
    public abstract function handle(array $args);
    
}