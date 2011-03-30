<?php
namespace Plugin\Imagine;
use Alloy;

/**
 * Imagine Plugin
 * Adds Imagine lib to your Alloy project
 * 
 * @version 0.1.4
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

        // Let autoloader know where to find Imagine library files
        $kernel->loader()->registerNamespace('Imagine', __DIR__ . '/lib');

        // Make methods globally avaialble with Kernel
        $kernel->addMethod('imagine', function($adapter = 'Gd') {
            $class = 'Imagine\\' . $adapter . '\Imagine';
            return new $class();
        });
    }
}