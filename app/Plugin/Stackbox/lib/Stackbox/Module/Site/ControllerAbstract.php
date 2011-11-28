<?php
namespace Stackbox\Module\Site;
use Stackbox;

/**
 * Base application module controller
 * Used as a base module class other modules must extend from
 */
abstract class ControllerAbstract extends Stackbox\Module\ControllerAbstract
{
    protected $_path;
    
    
    /**
     * Return current class path
     * Attempts to guess based on common 'modules' directory
     * Can be overridded by setting a varialbe to the $_path directoy on the extending class (like __DIR__)
     */
    public function path()
    {
        if(null !== $this->_path) {
            return $this->_path;
        }

        $class = get_called_class();
        $path = str_replace('\\', '/', str_replace('\\Controller', '', $class));
        return $this->kernel->site()->dir() . 'content/' . $path;
    }
}