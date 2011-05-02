<?php
namespace Module\Page\Module;
use Stackbox;

class Entity extends Stackbox\EntityAbstract
{
    // Setup table and fields
    protected static $_datasource = "page_module_settings";
    
    /**
     * Fields
     */
    public static function fields() {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'site_id' => array('type' => 'int', 'index' => 'site_module', 'default' => 0),
            'module_id' => array('type' => 'int', 'index' => 'site_module', 'required' => true),
            'setting_key' => array('type' => 'string', 'required' => true),
            'setting_value' => array('type' => 'string', 'required' => true),
            'ordering' => array('type' => 'int', 'default' => 0)
        ) + parent::fields();
    }
}