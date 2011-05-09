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
     * 
     * @method GET
     */
    public function editlistAction(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        // Get all settings for current module
        $settings = $module->settings();
        
        // Return view template
        return $this->template(__FUNCTION__)
            ->set(compact('settings', 'page', 'module'));
    }
    
    
    /**
     * Update settings
     * 
     * @method POST
     */
    public function postMethod(Alloy\Request $request, Module\Page\Entity $page, Module\Page\Module\Entity $module)
    {
        $mapper = $this->kernel->mapper();

        // Steps:
        // - Loop over all settings to see which ones already exist and update them
        // - Add settings that don't exist

        $settings = $module->settings();


        var_dump($settings->toArray(), $request->post());

        echo __METHOD__;
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