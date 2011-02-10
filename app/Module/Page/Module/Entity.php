<?php
namespace Module\Page\Module;
use Stackbox;

class Entity extends Stackbox\EntityAbstract
{
    // Setup table and fields
    protected static $_datasource = "page_modules";
    
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
}