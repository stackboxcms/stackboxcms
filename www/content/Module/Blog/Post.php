<?php
class Module_Blog_Post extends Cx_Module_Entity_Abstract
{
	protected $_datasource = "module_blog_posts";
	
	// Fields
	public $title = array('type' => 'string', 'required' => true);
	public $description = array('type' => 'text');
	public $status = array('type' => 'int', 'default' => 1, 'required' => true);
	public $date_published = array('type' => 'datetime');
	public $date_created = array('type' => 'datetime');
	public $date_modified = array('type' => 'datetime');
}