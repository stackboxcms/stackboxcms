<?php
class Module_Blog_Mapper extends Cx_Module_Mapper
{
	protected $_datasource = "module_blog";
	
	// Fields
	public $title = array('type' => 'text', 'required' => true);
}