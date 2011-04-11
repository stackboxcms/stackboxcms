<?php
namespace Plugin\Finder;
use Alloy;

/**
 * Finder Plugin
 * Adds the finder and file iterator from Symfony2 PR6
 */
class Plugin
{
    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;

        // Let autoloader know where to find Spot library files
        $kernel->loader()->registerNamespace('Symfony\Component\Finder', __DIR__ . '/lib');

        // Make methods globally avaialble with Kernel
        $kernel->addMethod('finder', function() {
            return new \Symfony\Component\Finder\Finder();
        });
    }
}