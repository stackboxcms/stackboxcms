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

        // Add Stackbox and Nijikodo classes to the load path
        $kernel->loader()->registerNamespace('Stackbox', __DIR__ . '/lib');
        $kernel->loader()->registerNamespace('Nijikodo', __DIR__ . '/lib');

        // Get current config settings
        $cfg = $kernel->config();
        $app = $cfg['app'];

        // @todo Determine which site HOST is and set site_id
        // @todo Set file paths with current site_id
        $hostname = $kernel->request()->server('HTTP_HOST');

        // Get site by hostname
        try {
            $siteMapper = $kernel->mapper('Module\Site\Mapper');
            $site = $siteMapper->getSiteByDomain($hostname);
        } catch(\Exception $e) {
            $content = $kernel->dispatch('page', 'install');
            echo $content;
            exit();
        }

        // Site not found - no hostname match
        if(!$site) {
            throw new \Stackbox\Exception\SiteNotFound("Site <b>" . $hostname . "</b> not found.");
        }

        // Make site object available on Kernel
        $kernel->addMethod('site', function() use($site) {
            return $site; 
        });

        // Set site files directory based on id
        $siteFilesDir = 'site/' . $site->id . '/';

        // Add config settings
        $kernel->config(array(
            'cms' => array(
                'site' => array(
                    'id' => $site->id,
                    'title' => $site->title
                ),
                'dir' => array(
                    'modules' => 'content/',
                    'themes' => 'themes/',
                    'files' => $siteFilesDir,
                    'assets_admin' => $cfg['app']['dir']['assets'] . 'admin/'
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
                    'modules' => $app['path']['root'] . $app['dir']['www'] . 'content/',
                    'themes' => $app['path']['root'] . $app['dir']['www'] . 'themes/',
                    'files' => $app['path']['root'] . $app['dir']['www'] . $siteFilesDir
                ),
                'url' => array(
                    'assets_admin' => $cfg['url']['root'] . str_replace($app['dir']['www'], '', $cfg['cms']['dir']['assets_admin']),
                    'themes' => $cfg['url']['root'] . str_replace($app['dir']['www'], '', $cfg['cms']['dir']['themes']),
                    'files' => $cfg['url']['root'] . str_replace($app['dir']['www'], '', $cfg['cms']['dir']['files'])
                )
            )
        ));

        // This adds to the load path because it already exists (does not replace it)
        $kernel->loader()->registerNamespace('Module', $kernel->config('cms.path.modules'));

        // Layout / API output
        $kernel->events()->addFilter('dispatch_content', 'cms_layout_api_output', array($this, 'layoutOrApiOutput'));

        // If debugging, track execution time and memory usage
        if($kernel->config('app.mode.development') || $kernel->config('app.debug')) {
            $timeStart = microtime(true);
            $kernel->events()->bind('boot_stop', 'cms_bench_time', function() use($timeStart) {
                $timeEnd = microtime(true);
                $timeDiff = $timeEnd - $timeStart;
                echo "\n<!-- Stackbox Execution Time: " . number_format($timeDiff, 6) . "s -->";
                echo "\n<!-- Stackbox Memory Usage:   " . number_format(memory_get_peak_usage(true) / (1024*1024), 2) . " MB -->\n\n";
            });
        }

        // Add sub-plugins and other plugins Stackbox depends on
        $kernel->plugin('Stackbox_User');
        $kernel->plugin('Module\Filebrowser');
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
            $layout->path($kernel->config('app.path.layouts'))
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