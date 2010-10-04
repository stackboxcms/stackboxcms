<?php
class Module_Blog_Entity extends Cx_Module_Entity_Abstract
{
	protected $_datasource = "module_blog";
	
	// Fields
	public $title = array('type' => 'string', 'required' => true);
	public $description = array('type' => 'text');
}