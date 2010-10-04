<?php
abstract class Cx_Module_Entity_Abstract extends Cx_Entity_Abstract
{
	public $id = array('type' => 'int', 'primary' => true, 'serial' => true);
	public $module_id = array('type' => 'int', 'index' => true, 'required' => true);
}