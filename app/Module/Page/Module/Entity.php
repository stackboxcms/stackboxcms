<?php
namespace Module\Page\Module;

class Entity extends \Cx\EntityAbstract
{
    // Setup table and fields
    protected static $_datasource = "page_modules";
    
    // Fields
    protected $id = array('type' => 'int', 'primary' => true, 'serial' => true);
    protected $site_id = array('type' => 'int', 'index' => 'site_page', 'default' => 0);
    protected $page_id = array('type' => 'int', 'index' => 'site_page', 'required' => true);
    protected $module_id = array('type' => 'int', 'default' => 0);
    protected $region = array('type' => 'string', 'required' => true);
    protected $name = array('type' => 'string', 'required' => true);
    protected $ordering = array('type' => 'int', 'default' => 0);
    protected $is_active = array('type' => 'boolean', 'default' => true);
    protected $date_created = array('type' => 'datetime');
}