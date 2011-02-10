<?php
namespace Stackbox\Module;
use Stackbox;

abstract class EntityAbstract extends Stackbox\EntityAbstract
{
    protected $id = array('type' => 'int', 'primary' => true, 'serial' => true);
    protected $module_id = array('type' => 'int', 'index' => true, 'required' => true);
}