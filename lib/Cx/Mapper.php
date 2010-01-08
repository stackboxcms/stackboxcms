<?php
/**
 * Base Mapper that module mappers will extend
 * 
 * Dependencies:
 *	- phpDataMapper
 */
abstract class Cx_Mapper extends phpDataMapper_Base
{
	protected $_entityClass = 'Cx_Mapper_Entity';
	
	
	/**
	 * Custom constructor for auto-migrations
	 */
	public function __construct(phpDataMapper_Adapter_Interface $adapter, $adapterRead = null)
	{
		// Parent constructor
		parent::__construct($adapter, $adapterRead);
		
		// Auto-migrate when in 'development' mode
		if(cx()->config('cx.mode.development') === true) {
			$this->migrate();
		}
	}
}