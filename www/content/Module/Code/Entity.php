<?php
class Module_Code_Entity extends Cx_Module_Entity_Abstract
{
	// Table
	protected $_datasource = "module_code";
	
	// Fields
	public $content = array('type' => 'text', 'required' => true);
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
}