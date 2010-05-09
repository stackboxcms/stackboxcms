<?php
/**
 * Base Cont-xt Mapper
 */
abstract class Cx_Mapper_Abstract extends Spot_Mapper_Abstract
{	
	// Site id for future multi-site possibilities
	public $site_id = array('type' => 'int', 'index' => true, 'default' => 0);
	
	
	/**
	 * Set site_id with current site based on config
	 */
	public function beforeSave(Spot_Entity $entity)
	{
		$entity->site_id = Alloy()->config('site.id', 0);
	}
}