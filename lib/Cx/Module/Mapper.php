<?php
/**
 * Base Module Mapper
 */
abstract class Cx_Module_Mapper extends Alloy_Module_Mapper
{
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $module_id = array('type' => 'int', 'index' => true, 'required' => true);
	
	// Site id for future multi-site possibilities
	public $site_id = array('type' => 'int', 'index' => true, 'default' => 0);
}