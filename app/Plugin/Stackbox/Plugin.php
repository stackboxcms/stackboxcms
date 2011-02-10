<?php
namespace Plugin\Stackbox;
use Alloy;

/**
 * Stack Plugin
 * Enables main CMS hooks and ensures classes are autoloaded
 */
class Plugin
{
    protected $kernel;


    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;

        // Add config settings
        $cfg = $kernel->config();
        $kernel->config(array(
            'stackbox' => array(
                'dir' => array(
                    'modules' => $cfg['dir']['www'] . 'content/',
                    'themes' => $cfg['dir']['www'] . 'themes/',
                    'assets_admin' => $cfg['dir']['assets'] . 'admin/'
                ),

                'default' => array(
                    'module' => 'page',
                    'action' => 'index',
                    'theme' => 'default',
                    'theme_template' => 'index'
                )
            )
        ));

        // Get config again because we need to use settings we just added
        $cfg = $kernel->config();
        $kernel->config(array(
            'stackbox' => array(
                'path' => array(
                    'modules' => $cfg['path']['root'] . $cfg['dir']['www'] . 'content/',
                    'themes' => $cfg['path']['root'] . $cfg['dir']['www'] . 'themes/'
                ),
                'url' => array(
                    'assets_admin' => $cfg['dir']['www'] . $cfg['stackbox']['dir']['assets_admin'],
                    'themes' => $cfg['dir']['www'] . $cfg['stackbox']['dir']['themes']
                )
            )
        ));

        // Add Stackbox and Nijikodo classes to the load path
        $kernel->loader()->registerNamespace('Stackbox', __DIR__ . '/lib');
        $kernel->loader()->registerNamespace('Nijikodo', __DIR__ . '/lib');

        // This adds to the load path because it already exists (does not replace it)
        $kernel->loader()->registerNamespace('Module', $kernel->config('stackbox.path.modules'));

        // Ensure API type output is served correctly
        $kernel->events()->addFilter('dispatch_content', 'sb_api_output', array($this, 'apiOutput'));
    }


    /**
     * Ensure API type output is served correctly
     */
    public function apiOutput($content)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();

        // Send correct response
        if(in_array($request->format, array('json', 'xml'))) {
            $response = $kernel->response();

            // No cache and hide potential errors
            ini_set('display_errors', 0);
            $response->header("Expires", "Mon, 26 Jul 1997 05:00:00 GMT"); 
            $response->header("Last-Modified", gmdate( "D, d M Y H:i:s" ) . "GMT"); 
            $response->header("Cache-Control", "no-cache, must-revalidate"); 
            $response->header("Pragma", "no-cache");
            
            // Correct content-type
            if('json' == $request->format) {
                $response->contentType('application/json');
            } elseif('xml' == $request->format) {
                $response->contentType('text/xml');
            }
        }

        return $content;
    }
}