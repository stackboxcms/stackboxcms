<?php
namespace Module\Page\Module;
use Stackbox;

class Entity extends Stackbox\EntityAbstract
{
    // Setup table and fields
    protected static $_datasource = "page_modules";
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
     * Relations
     */
    /*
    public static function relations() {
        return array(
            // Settings as key => value
            'settings' => array(
                'type' => 'HasManyKV', // HasMany Key/Values
                'entity' => 'Module\Page\Module\Settings\Entity',
                'keyField' => 'setting_key',
                'valueField' => 'setting_value',
                'where' => array('site_id' => ':entity.site_id', 'module_id' => ':entity.id')
            )
        ) + parent::relations();
    }
    */


    /**
     * Get and return settings in special collction with direct access to settings by 'setting_key' name
     */
    public function settings()
    {
        if($this->_settings) {
            return $this->_settings;
        }

        $kernel = \Kernel();
        $mapper = $kernel->mapper();
        $settings = $mapper->all('Module\Page\Module\Settings\Entity')
            ->where(array('site_id' => $this->site_id, 'module_id' => $this->id))
            ->order(array('ordering' => 'ASC'));

        $this->_settings = new Settings\Collection($settings);
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
}