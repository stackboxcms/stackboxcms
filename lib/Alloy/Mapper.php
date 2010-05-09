<?php
/**
 * Base Mapper that module mappers will extend
 * 
 * Dependencies:
 *	- Spot
 */
abstract class Alloy_Mapper extends Spot_Mapper_Abstract
{
	protected static $_migrateDone = array();

	/**
	 * Custom initialization for auto-migrations
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
}