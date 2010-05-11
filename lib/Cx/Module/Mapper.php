<?php
/**
 * Base Module Mapper
 */
abstract class Cx_Module_Mapper extends Cx_Mapper_Abstract
{
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $module_id = array('type' => 'int', 'index' => true, 'required' => true);
}