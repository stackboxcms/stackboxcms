<?php
namespace Stackbox\Module;
use Stackbox;

abstract class EntityAbstract extends Stackbox\EntityAbstract
{
    /**
	 * Base fields on all CMS module entities
	 */
	public static function fields()
	{
		// Site id for multi-site installations
		return array(
    		'module_id' => array('type' => 'int', 'index' => true, 'required' => true)
		) + parent::fields();
	}
}