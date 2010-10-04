<?php
/**
 * Base Cont-xt Entity
 */
abstract class Cx_Entity_Abstract
{
	// Site id for future multi-site possibilities
	public $site_id = array('type' => 'int', 'index' => true, 'default' => 0);
	
	
	/**
	 * Set site_id with current site based on config
	 */
	public function beforeSave(Spot_Mapper $mapper)
	{
		$this->site_id = Alloy()->config('site.id', 0);
	}
}