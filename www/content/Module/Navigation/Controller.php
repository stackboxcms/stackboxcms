<?php
namespace Module\Navigation;

/**
 * Navigation Module
 */
class Controller extends \Cx\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction($request, $page, $module)
    {
        $pages = $this->kernel->mapper('Module\Page\Mapper')->pageTree();
        
        return $this->view(__FUNCTION__)
            ->set(array('pages' => $pages));
    }
    
    
    /**
     * @method GET
     */
    public function editAction($request, $page, $module)
    {
        return "There are currently no editable options for navigation display.";
    }
}