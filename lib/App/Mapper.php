<?php
/**
 * Base Mapper that module mappers will extend
 * 
 * Dependencies:
 *	- phpDataMapper
 * 	- Spot
 */
abstract class App_Mapper extends phpDataMapper_Model
{
	public function __construct(phpDataMapper_Database_Adapter_Interface $adapter)
	{
		// Parent constructor
		parent::__construct($adapter);
		
		// Auto-migrate when in 'development' mode
		if(Spot()->config('mode.development') === true) {
			$this->migrate();
		}
	}
}