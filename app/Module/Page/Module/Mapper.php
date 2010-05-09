<?php
class Module_Page_Module_Mapper extends Cx_Mapper_Abstract
{
	// Custom row class
	protected $_entityClass = 'Module_Page_Module_Entity';
	
	// Setup table and fields
	protected $_datasource = "page_modules";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $page_id = array('type' => 'int', 'key' => true, 'required' => true);
	public $module_id = array('type' => 'int', 'default' => 0);
	public $region = array('type' => 'string', 'required' => true);
	public $name = array('type' => 'string', 'required' => true);
	public $ordering = array('type' => 'int', 'default' => 0);
	public $is_active = array('type' => 'boolean', 'default' => true);
	public $date_created = array('type' => 'datetime');
}


// Custom entity object
class Module_Page_Module_Entity extends Cx_Module_Entity
{
	
}