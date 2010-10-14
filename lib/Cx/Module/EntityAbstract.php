<?php
namespace Cx\Module;

abstract class EntityAbstract extends \Cx\EntityAbstract
{
    protected $id = array('type' => 'int', 'primary' => true, 'serial' => true);
    protected $module_id = array('type' => 'int', 'index' => true, 'required' => true);
}