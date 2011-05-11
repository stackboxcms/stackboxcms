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
        $pages = $this->kernel->mapper('Module\Page\Mapper')->pageTree();
        
        return $this->template(__FUNCTION__)
            ->set(array('pages' => $pages));
    }
    
    
    /**
     * @method GET
     */
    public function editlistAction($request, $page, $module)
    {
        return "There are currently no editable options for navigation display.";
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
                'start_page' => array(
                    'type' => 'select',
                    'options' => array(0 => '[None]'),
                    'default' => 0,
                    'after' => 'Page navigation will start from'
                ),
                'display_level' => array(
                    'type' => 'int',
                    'default' => 0,
                    'after' => 'Minimum navigation level to begin displaying pages (0 for all levels)'
                )
            )
        );
    }
}