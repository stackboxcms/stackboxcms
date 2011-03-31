<?php
namespace Plugin\Stackbox;
use Alloy;

/**
 * Stackbox Plugin
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
        $siteFilesDir = 'site/' . $cfg['site']['id'] . '/';

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
                    'themes' => $cfg['url']['root'] . str_replace($cfg['dir']['www'], '', $cfg['cms']['dir']['themes']),
                    'files' => $cfg['url']['root'] . str_replace($cfg['dir']['www'], '', $cfg['cms']['dir']['files'])
                )
            )
        ));

        // Add Stackbox and Nijikodo classes to the load path
        $kernel->loader()->registerNamespace('Stackbox', __DIR__ . '/lib');
        $kernel->loader()->registerNamespace('Nijikodo', __DIR__ . '/lib');

        // This adds to the load path because it already exists (does not replace it)
        $kernel->loader()->registerNamespace('Module', $kernel->config('cms.path.modules'));

        // Layout / API output
        $kernel->events()->addFilter('dispatch_content', 'cms_layout_api_output', array($this, 'layoutOrApiOutput'));

        // Add sub-plugins
        $kernel->plugin('Stackbox_User');
    }


    /**
     * Ensure layout or API type output is served correctly
     */
    public function layoutOrApiOutput($content)
    {
        $kernel = $this->kernel;
        $request = $kernel->request();
        $response = $kernel->response();

        $response->contentType('text/html');

        $layoutName = null;
        if($content instanceof Alloy\View\Template) {
            $layoutName = $content->layout();
        }

        // Only if layout is explicitly given
        if($layoutName) {
            $layout = new \Alloy\View\Template($layoutName, $request->format);
            $layout->path($kernel->config('path.layouts'))
                ->format($request->format);

            // Ensure layout exists
            if (false === $layout->exists()) {
                return $content;
            }

            // Pass along set response status and data if we can
            if($content instanceof Alloy\Module\Response) {
                $layout->status($content->status());
                $layout->errors($content->errors());
            }

            // Pass set title up to layout to override at template level
            if($content instanceof Alloy\View\Template) {
                // Force render layout so we can pull out variables set in template
                $contentRendered = $content->content();
                $layout->head()->title($content->head()->title());
                $content = $contentRendered;
            }

            $layout->set(array(
                'kernel'  => $kernel,
                'content' => $content
            ));

            return $layout;
        }

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