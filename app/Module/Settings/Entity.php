<?php
namespace Module\Settings;

use Spot;
use Stackbox;

class Entity extends Stackbox\EntityAbstract
{
    // Setup table and fields
    protected static $_datasource = "cms_settings";
    
    /**
     * Fields
     */
    public static function fields() {
        return array_merge(parent::fields(), array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'site_id' => array('type' => 'int', 'index' => 'site_type_id', 'required' => true),
            'type' => array('type' => 'string', 'index' => 'site_type_id', 'required' => true),
            'type_id' => array('type' => 'int', 'index' => 'site_type_id', 'default' => 0),
            'setting_key' => array('type' => 'string', 'required' => true),
            'setting_value' => array('type' => 'string') // Not "required" because values can be NULL
        ));
    }


    /**
     * Ensures 'type' and 'setting_key' are lowercase strings
     */
    public function beforeSave(Spot\Mapper $mapper)
    {
        $this->__set('type', strtolower($this->__get('type')));
        $this->__set('setting_key', strtolower($this->__get('setting_key')));
        return parent::beforeSave($mapper);
    }
}