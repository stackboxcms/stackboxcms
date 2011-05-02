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
}