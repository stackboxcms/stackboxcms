<?php
class Module_Text_Mapper extends Cx_Mapper_Module
{
	// Table
	protected $source = "module_text";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true);
	public $content = array('type' => 'text', 'required' => true);
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
	
	// Custom entity class
	protected $_entityClass = 'Module_Text_Entity';
}