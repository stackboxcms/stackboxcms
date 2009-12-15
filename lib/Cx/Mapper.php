<?php
/**
 * Base Mapper that module mappers will extend
 * 
 * Dependencies:
 *	- phpDataMapper
 * 	- Spot
 */
abstract class Cx_Mapper extends phpDataMapper_Base
{
	public function __construct(phpDataMapper_Adapter_Interface $adapter)
	{
		// Parent constructor
		parent::__construct($adapter);
		
		// Auto-migrate when in 'development' mode
		if(cx()->config('cx.mode.development') === true) {
			$this->migrate();
		}
	}
}