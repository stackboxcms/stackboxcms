<?php
namespace Plugin\Imagine;
use Alloy;

/**
 * Imagine Plugin
 * Adds Imagine lib to your Alloy project
 * 
 * @version 0.2.8
 * @see https://github.com/avalanche123/Imagine
 */
class Plugin
{
    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;

        // Require PHAR package
        require 'phar://' . __DIR__ . '/imagine.phar';

        // Make methods globally avaialble with Kernel
        $kernel->addMethod('imagine', function($adapter = 'Gd') {
            $class = 'Imagine\\' . $adapter . '\Imagine';
            return new $class();
        });
    }
}