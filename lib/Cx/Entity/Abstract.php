<?php
/**
 * Base Cont-xt Entity
 */
abstract class Cx_Entity_Abstract
{
	// Site id for future multi-site possibilities
	public $site_id = array('type' => 'int', 'index' => true, 'default' => 0);
	
	
	protected static $_migrateDone = array();
	
	/**
	 * Initialize and auto-migrate table structure in development mode
	 */
	public function init()
	{
		// Hack for MySQL to support UTF-8 properly
		if($this->adapter() instanceof Spot_Adapter_Mysql) {
			$this->adapter()->connection()->exec("SET NAMES 'utf8'");
		}
		
		 // Auto-migrate when in 'development' mode
		if(Alloy()->config('mode.development') === true) {
			if(!isset(self::$_migrateDone[get_class($this)])) {
				$this->migrate();
				self::$_migrateDone[get_class($this)] = true;
			}
		}
	}
	
	
	/**
	 * Set site_id with current site based on config
	 */
	public function beforeSave(Spot_Mapper $mapper)
	{
		$this->site_id = Alloy()->config('site.id', 0);
	}
}