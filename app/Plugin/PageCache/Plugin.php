<?php
namespace Plugin\PageCache;
use Alloy\PluginAbstract;

/**
 * PageCache Plugin
 * Enables full page caching to memcache
 */
class Plugin extends PluginAbstract
{
    protected $cache;


    /**
     * Initialize plugin
     */
    public function init()
    {
        // Add Doctrine\Common\Cache to load path
        $this->kernel->loader()
            ->registerNamespace('Doctrine\Common\Cache', __DIR__ . '/lib/Doctrine/Common/Cache');

        // Load Memcache connection
        $memcache = new \Memcache();
        $memcache->connect($this->config['host'], $this->config['port']);

        $cache = new \Doctrine\Common\Cache\MemcacheCache();
        $cache->setMemcache($memcache);

        // Save cache instance on object
        $this->cache = $cache;

        // Layout / API output
        $this->kernel->events()->bind('response_sent', 'pagecache_cache', array($this, 'cacheOutput'));
    }


    /**
     * Set cache key with full page output
     */
    protected function cacheOutput($output)
    {
        $user = $this->kernel->user();
        $request = $this->kernel->request();

        // Only cache if user is not logged in, and request is GET
        if(!$user->isLoggedIn() && $request->isGet()) {
            // Build key with current request info
            // Key Format: '$scheme$host$request_method$request_uri'
            $cacheKey = $request->scheme()
                        . $request->host()
                        . $request->method()
                        . $request->uri();

            // Cache output content
            $this->cache->set($cacheKey, (string) $response->content());
        }
    }
}

// Ensure Memcache extension is loaded
if(!class_exists('Memcache', false)) {
    throw new \Exception("The 'memcache' extension must be installed and enabled to using " . __NAMESPACE__ . ".");
}
