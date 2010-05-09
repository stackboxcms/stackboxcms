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
	
	
	/**
	 * Set site_id with current site based on config
	 */
	public function beforeSave(Cx_Module_Entity $entity)
	{
		$entity->site_id = Alloy()->config('site.id', 0);
	}
}