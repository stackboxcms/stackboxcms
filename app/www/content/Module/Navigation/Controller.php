<?php
namespace Module\Navigation;
use Stackbox;

/**
 * Navigation Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        if('section' == $module->setting('type')) {
            $pages = $this->kernel->mapper('Module\Page\Mapper')->pageTree($page);
        } else {
            $pages = $this->kernel->mapper('Module\Page\Mapper')->pageTree();
        }
        
        return $this->template(__FUNCTION__)
            ->set(compact('pages', 'module'));
    }
    
    
    /**
     * @method GET
     */
    public function editlistAction($request, $page, $module)
    {
        return $this->settingsAction($request, $page, $module);
    }


    /**
     * Settings init
     * 
     * Define all settings fields and values that will be needed
     */
    public function settings($page, $module)
    {
        return array(
            // Group
            'display' => array(
                // Fields
                'type' => array(
                    'type' => 'select',
                    'options' => array(
                        'full' => 'Full Tree (All Pages)',
                        'section' => 'Section - All child pages of current active page'
                    ),
                    'default' => 'full',
                    'after' => 'Page navigation will start from'
                ),
                'show_only_active' => array(
                    'type' => 'boolean',
                    'default' => true,
                    'after' => 'Show only "active" pages'
                ),
                /*
                // @todo Have to fill-in with page tree in dropdown
                'start_page' => array(
                    'type' => 'select',
                    'options' => array(0 => '[None]'),
                    'default' => 0,
                    'after' => 'Page navigation will start from'
                ),
                */
                'show_homepage' => array(
                    'type' => 'boolean',
                    'default' => true,
                    'after' => 'Show Homepage in menu?'
                ),
                'level_min' => array(
                    'type' => 'int',
                    'default' => null,
                    'after' => 'Minimum navigation level to begin displaying pages (0 for root level)'
                ),
                'level_max' => array(
                    'type' => 'int',
                    'default' => null,
                    'after' => 'Maximum navigation level to display pages for (0 for no limit)'
                ),
                'css_ul_root' => array(
                    'type' => 'string',
                    'default' => null,
                    'title' => 'Root CSS Class',
                    'after' => 'CSS Class to apply to the root &lt;ul&gt; node'
                ),
            )
        );
    }
}