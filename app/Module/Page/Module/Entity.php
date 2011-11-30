<?php
namespace Module\Page\Module;
use Stackbox;

class Entity extends Stackbox\EntityAbstract
{
    // Setup table and fields
    protected static $_datasource = "cms_page_modules";
    protected $_settings;
    
    /**
     * Fields
     */
    public static function fields() {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'site_id' => array('type' => 'int', 'index' => 'site_page', 'default' => 0),
            'page_id' => array('type' => 'int', 'index' => 'site_page', 'required' => true),
            'region' => array('type' => 'string', 'required' => true),
            'name' => array('type' => 'string', 'required' => true),
            'ordering' => array('type' => 'int', 'default' => 0),
            'is_active' => array('type' => 'boolean', 'default' => true),
            'date_created' => array('type' => 'datetime')
        ) + parent::fields();
    }


    /**
     * Get and return settings in special collction with direct access to settings by 'setting_key' name
     */
    public function settings()
    {
        if(null !== $this->_settings) {
            return $this->_settings;
        }

        $kernel = \Kernel();
        $this->_settings = $kernel->mapper('Module\Settings\Mapper')->getSettingsForModule($this->name, $this->id);
        return $this->_settings;
    }


    /**
     * Get setting value by key name
     */
    public function setting($key, $default = null)
    {
        $settings = $this->settings();
        if($v = $settings->$key) {
            return $v;
        }
        return $default;
    }

    /**
     * Get lowercase module name
     */
    public function name()
    {
        return strtolower($this->name);
    }
}