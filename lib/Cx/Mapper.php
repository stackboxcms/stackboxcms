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
}