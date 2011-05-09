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
        $settings = $module->settings()->toArray();
        
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
        $postSettings = $request->post(); // POST payload
        $mapper = $this->kernel->mapper();

        // Steps:
        // - Loop over all settings to see which ones already exist and update them
        // - Add settings that don't exist

        $settings = $module->settings();
        $currentSettings = $settings->toArray();

        $updateSettings = array_intersect_key($postSettings, $currentSettings);
        $insertSettings = array_diff_key($postSettings, $currentSettings);

        // UPDATE settings that already exist
        foreach($settings as $setting) {
            // Update with value from POST data
            $setting->setting_value = $updateSettings[$setting->setting_key];
            $mapper->save($setting);
        }

        // INSERT settings that don't already exist
        foreach($insertSettings as $key => $value) {
            $setting = new Entity();
            $setting->data(array(
                'site_id' => $module->site_id,
                'module_id' => $module->id,
                'setting_key' => $key,
                'setting_value' => $value
            ));
            $mapper->save($setting);
        }

        //return $this->response("Settings have been updated");

        return $this->kernel->redirect($this->kernel->url(array(
                'module_name' => $module->name,
                'module_id' => (int) $module->id),
            'module'));
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