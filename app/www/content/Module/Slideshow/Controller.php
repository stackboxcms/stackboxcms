<?php
namespace Module\Slideshow;
use Alloy, Module, Stackbox;

/**
 * Slideshow Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    /**
     * @method GET
     */
    public function indexAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        return \Kernel()->dispatch('Slideshow_Item', 'indexAction', array($request, $page, $module));
    }
    
    
    /**
     * @method GET
     */
    public function editAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        // Get all blog posts (remember - query is not actually executed yet and can be futher modified by the gridview)
        $mapper = $this->kernel->mapper();
        $items = $mapper->all('Module\Slideshow\Item\Entity');
        
        // Return view template
        return $this->template(__FUNCTION__)
            ->set(compact('items', 'page', 'module'));
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\Slideshow\Item\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Slideshow\Item\Entity');
    }
}
