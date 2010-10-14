<?php
namespace Module\Code;

class Entity extends \Cx\Module\EntityAbstract
{
    // Table
    protected static $_datasource = "module_code";
    
    // Fields
    protected $content = array('type' => 'text', 'required' => true);
    protected $type = array('type' => 'string');
    protected $date_created = array('type' => 'datetime');
    protected $date_modified = array('type' => 'datetime');
}