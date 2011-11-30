<?php
namespace Module\Settings;

use Stackbox;
use Alloy\Request;
use Module\Page\Entity as Page;
use Module\Page\Module\Entity as Module;

/**
 * Page Module Settings Module
 */
class Controller extends Stackbox\Module\ControllerAbstract
{
    const ENTITY = 'Module\Settings\Entity';

    // Path override
    protected $_path = __DIR__;


    /**
     * Public listing of slideshow items
     * @method GET
     */
    public function indexAction(Request $request, Page $page, Module $module)
    {
        return false;
    }


    /**
     * Edit list for admin view
     * 
     * @method GET
     */
    public function editlistAction(Request $request, Page $page, Module $module)
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
    public function postMethod(Request $request, Page $page, Module $module)
    {
        $mapper = $this->kernel->mapper();
        $target_module_name = $request->target_module_name;
        $postSettings = $request->post(); // POST payload
        if(isset($postSettings['target_module_name'])) {
            unset($postSettings['target_module_name']);
        }

        // Steps:
        // - Loop over all settings to see which ones already exist and update them
        // - Add settings that don't exist
        if('site' == $target_module_name) {
            $settings = $this->kernel->site()->settings();
        } elseif('page' == $target_module_name) {
            $settings = $page->settings();
        } else {
            $settings = $module->settings();
        }
        $currentSettings = $settings->toArray();

        $updateSettings = array_intersect_key($postSettings, $currentSettings);
        $insertSettings = array_diff_key($postSettings, $currentSettings);

        // UPDATE settings that already exist
        foreach($settings as $setting) {
            // Update with value from POST data
            $setting->setting_value = isset($updateSettings[$setting->setting_key]) ? $updateSettings[$setting->setting_key] : null;
            $mapper->save($setting);
        }

        // INSERT settings that don't already exist
        foreach($insertSettings as $key => $value) {
            $setting = new Entity();
            $setting->data(array(
                'site_id' => $module->site_id,
                'type' => $target_module_name,
                'type_id' => (int) $module->id,
                'setting_key' => $key,
                'setting_value' => $value
            ));
            $mapper->save($setting);
        }

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
        $this->kernel->mapper()->migrate(self::ENTITY);
        return parent::install($action, $params);
    }
    
    
    /**
     * Uninstall Module
     *
     * @see \Stackbox\Module\ControllerAbstract
     */
    public function uninstall()
    {
        return $this->kernel->mapper()->dropDatasource(self::ENTITY);
    }
}