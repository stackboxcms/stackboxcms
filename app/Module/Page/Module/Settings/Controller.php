<?php
namespace Module\Page\Module\Settings;
use Alloy, Module, Stackbox;

/**
 * Page Module Settings Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    // Path override
    protected $_path = __DIR__;


    /**
     * Public listing of slideshow items
     * @method GET
     */
    public function indexAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        return false;
    }


    /**
     * Edit list for admin view
     * @method GET
     */
    public function editlistAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        // Get all settings for current module
        //$mapper = $this->kernel->mapper();
        //$items = $mapper->all('Module\Slideshow\Item');
        
        // Return view template
        return $this->template(__FUNCTION__)
            ->set(compact('settings', 'page', 'module'));
    }
    
    
    /**
     * Create a new resource with the given parameters
     * @method POST
     */
    public function postMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        // @todo Loop over ALL settings for current module and save them with passed values
    }
    
    
    /**
     * @method DELETE
     */
    public function deleteMethod($request, $page, $module)
    {
        $mapper = $this->kernel->mapper();
        $item = $mapper->get('Module\Slideshow\Item', $request->module_item);
        if(!$item) {
            return false;
        }
        return $mapper->delete($item);
    }
    
    
    /**
     * Install Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function install($action = null, array $params = array())
    {
        $this->kernel->mapper()->migrate('Module\Page\Module\Settings\Entity');
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource('Module\Page\Module\Settings\Entity');
    }
}
