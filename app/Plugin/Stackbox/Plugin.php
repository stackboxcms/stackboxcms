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

        // Get current config settings
        $cfg = $kernel->config();

        // @todo Determine which site HOST is and set site_id
        // @todo Set file paths with current site_id
        $siteFilesDir = 'files/' . $cfg['site']['id'] . '/';

        // Add config settings
        $kernel->config(array(
            'cms' => array(
                'dir' => array(
                    'modules' => 'content/',
                    'themes' => 'themes/',
                    'files' => $siteFilesDir,
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
            'cms' => array(
                'path' => array(
                    'modules' => $cfg['path']['root'] . $cfg['dir']['www'] . 'content/',
                    'themes' => $cfg['path']['root'] . $cfg['dir']['www'] . 'themes/',
                    'files' => $cfg['path']['root'] . $cfg['dir']['www'] . $siteFilesDir
                ),
                'url' => array(
                    'assets_admin' => $cfg['url']['root'] . str_replace($cfg['dir']['www'], '', $cfg['cms']['dir']['assets_admin']),
                    'themes' => $cfg['url']['root'] . $cfg['cms']['dir']['themes'],
                    'files' => $cfg['url']['root'] . $cfg['cms']['dir']['files']
                )
            )
        ));

        // Add Stackbox and Nijikodo classes to the load path
        $kernel->loader()->registerNamespace('Stackbox', __DIR__ . '/lib');
        $kernel->loader()->registerNamespace('Nijikodo', __DIR__ . '/lib');

        // This adds to the load path because it already exists (does not replace it)
        $kernel->loader()->registerNamespace('Module', $kernel->config('cms.path.modules'));

        // Ensure API type output is served correctly
        $kernel->events()->addFilter('dispatch_content', 'cms_api_output', array($this, 'apiOutput'));

        // Add sub-plugins
        $kernel->plugin('Stackbox_User');
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